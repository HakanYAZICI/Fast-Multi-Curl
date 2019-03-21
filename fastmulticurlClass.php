class fastmulticurl {

<?

//curl yapılacak url adreslerini array olarak fonksiyona aktarmalısın.

 public function MultiCurl($sources)
    {
		
		//bu ayarların açıklamasına bakıp tek tek düzenleyebilirsin.
        $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_IPRESOLVE => true,
            CURL_IPRESOLVE_V4 => true,
            CURLOPT_ENCODING => '',
            CURLOPT_RETURNTRANSFER => true,
			//timeout ayarları önemli, burada 3 saniyeyi aşan bağlantılar yok sayılıyor. istediğin gibi güncelleyebilirsin. 
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_MAXREDIRS => 1,
			//güvenlik sertifikası kontrolü yapmaya yarar.
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36"
        );

        $master = curl_multi_init();

        foreach ($sources as $source_id => $source) {
            $sources_array[$source_id] = curl_init($source);
            curl_setopt_array($sources_array[$source_id], $curl_options);
            curl_multi_add_handle($master, $sources_array[$source_id]);
        }

        do {
            curl_multi_exec($master, $running);
        } while ($running > 0);
        foreach ($sources as $source_id => $source) {
			//sonucun http kodunu ["header"]["http_code"] anahtarına aktarır
            $results[$source_id]["header"]["http_code"] = curl_getinfo($sources_array[$source_id]) ["http_code"];
			//sonuca zaman damgası ekler
            $results[$source_id]["header"]["time"] = time();
			//gelen veriyi her kaynak için [data] anahtarına aktarır.
            $results[$source_id]["data"] = json_decode(curl_multi_getcontent($sources_array[$source_id]), true);
			
			//200 dışındaki tüm sonuçları $results dizisinden çıkarır.
            if ((($results[$source_id]["header"]["http_code"]) != 200) && !is_array($results[$source_id]["data"])) {
                unset($results[$source_id]);
            }


        }

        return $results;
    }

}
