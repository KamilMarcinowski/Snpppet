<?php

require 'vendor/autoload.php'; // Załaduj bibliotekę

use Discord\Discord;
use Discord\WebSockets\Intents;
use GuzzleHttp\Client;

$discord = new Discord([
    'token' => '', // Zastąp prawdziwym tokenem bota
]);

// Klucz API dla YouTube
$youtubeApiKey = ''; // Zastąp prawdziwym kluczem API YouTube

//searchYouTube("top music hits", $youtubeApiKey);

// Funkcja do wyszukiwania piosenek na YouTube
function searchYouTube($query, $apiKey) {
    $client = new Client();
    $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($query) . "&key=" . $apiKey;

    try {
        $response = $client->request('GET', $url);
        $data = json_decode($response->getBody()->getContents(), true);

        $videos = [];

        foreach ($data['items'] as $item) {
            if ($item['id']['kind'] === 'youtube#video') {
                $videos[] = [
                    'title' => $item['snippet']['title'],
                    'url' => 'https://www.youtube.com/watch?v=' . $item['id']['videoId'],
                    'thumbnail' => $item['snippet']['thumbnails']['default']['url']
                ];
            }
        }

        return $videos;

    } catch (Exception $e) {
        echo "Błąd podczas pobierania danych z YouTube: " . $e->getMessage();
        return [];
    }
}

$discord->on('ready', function (Discord $discord) use ($youtubeApiKey) {
    echo "Zalogowano jako {$discord->user->username}\n";

    // Określ kanał, na którym bot będzie wysyłał wiadomości (musisz znaleźć ID kanału)
    $channelId = '1263952732251881532'; // Zastąp ID kanału, na którym ma wysyłać wiadomości

    // Funkcja wysyłająca snippet piosenki co godzinę
    while (true) {
        // Wyszukaj piosenkę na YouTube (np. "top music hits")
        $query = "top music hits"; // Możesz dostosować zapytanie

        $videos = searchYouTube($query, $youtubeApiKey);

        var_dump($videos);

        if (count($videos) > 0) {
            $response = "Znalezione wideo:\n";
            $video = $videos[0]; // Wybierz pierwsze wideo

            // Wyślij wiadomość na Discorda z linkiem do filmu
            $discord->getChannel($channelId)->sendMessage("**{$video['title']}**\n{$video['url']}");
        } else {
           $discord->getChannel($channelId)->sendMessage("Nic ni ma");
        }

        // Czekaj 1 godzinę (3600 sekund)
        sleep(36);
    }
});

$discord->run();

?>
