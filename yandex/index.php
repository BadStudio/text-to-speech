<?
require_once "defines.php";

$url = "https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize";
$headers = ['Authorization: Bearer ' . TOKEN];

$text = "Нейросети уже заменяют программистов?";
$text .= "Я решил не отставать и тоже переквалифицироваться. Теперь я бармен!";
$text .= "Как говорится, если не можешь победить технологии, присоединяйся к ним...";
$text .= "и наливай Пина Коладу!";
$text .= "Давайте поднимем стаканы за нашу новую роботизированную эру!";
$text .= "Хотя, честно говоря, я даже не знаю, как делать мартини...";
$text .= "Кто-нибудь здесь умеет???";

$text = "Всем привет! Это команда Бэд Студио и мы сегодня покажем вам новую работу!";

$arVoices = ['ermil', 'filipp', 'madirus', 'zahar', 'omazh', 'jane', 'alena'];
$arEmotions = ['neutral', 'good', 'evil'];
$fileName = "demo";

// Путь к директории, куда будем сохранять файлы
$dirPath = "result/" . $fileName . "/";

// Если директория не существует, то создаем ее
if (!file_exists($dirPath)) {
    mkdir($dirPath, 0777, true);
}

foreach ($arVoices as $selectVoice) {
    foreach ($arEmotions as $selectEmotion) {
        $speed = '1';

        switch ($selectEmotion) {
            case "good":
                switch ($selectVoice) { //эти голоса не поддерживают good
                    case "omazh":
                        $selectEmotion = $arEmotions[0];
                        break;
                }
                break;
            case "evil":
                switch ($selectVoice) { //эти голоса не поддерживают evil
                    case "ermil":
                    case "zahar":
                    case "alena":
                        $selectEmotion = $arEmotions[0];
                }
                break;
        }

        $post = [
            'text' => $text,
            'folderId' => FOLDER,
            'lang' => 'ru-RU',
            'voice' => $selectVoice,
            'emotion' => $selectEmotion,
            'speed' => $speed,
            'format' => 'mp3',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($post !== false) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            print "Error: " . curl_error($ch);
        }
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            $decodedResponse = json_decode($response, true);
            echo "Error code: " . $decodedResponse["error_code"] . "\r\n";
            echo "Error message: " . $decodedResponse["error_message"] . "\r\n";
        } else {
            $speed = str_replace('.', '', $speed);
            $filePath = $dirPath . $fileName . "_" . $selectVoice . "_" . $selectEmotion . "_" . $speed . ".mp3";
            file_put_contents($filePath, $response);
        }
        curl_close($ch);
    }
}