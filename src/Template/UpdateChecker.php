<?php

namespace App\Template;

class UpdateChecker
{
    public function newestVersion(): string
    {
        $url = 'https://api.github.com/repos/jasperweyne/helpless-kiwi/releases/latest';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'helpless-kiwi',
        ]);

        $rawResponse = curl_exec($curl);
        curl_close($curl);
        assert(is_string($rawResponse));

        $response = json_decode($rawResponse, true);
        assert(is_array($response));

        return $response['tag_name'];
    }
}
