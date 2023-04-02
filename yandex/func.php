<?
require_once "defines.php";

function TextToSpeech($text = false)
{
    $result = "";
    if ($text) {
        $url = "https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize";
        $headers = ['Authorization: Bearer ' . TOKEN];

        /*
        $text = "Нейросети уже заменяют программистов?";
        $text .= "Я решил не отставать и тоже переквалифицироваться. Теперь я бармен!";
        $text .= "Как говорится, если не можешь победить технологии, присоединяйся к ним...";
        $text .= "и наливай Пина Коладу!";
        $text .= "Давайте поднимем стаканы за нашу новую роботизированную эру!";
        $text .= "Хотя, честно говоря, я даже не знаю, как делать мартини...";
        $text .= "Кто-нибудь здесь умеет???";

        $text = "Всем привет! Это команда Бэд Студио и мы сегодня покажем вам новую работу!";
        */

        $arVoices = ['ermil', 'filipp', 'madirus', 'zahar', 'omazh', 'jane', 'alena'];
        $arEmotions = ['neutral', 'good', 'evil'];
        $fileName = "audio_" . time();

// Путь к директории, куда будем сохранять файлы
        $dirPath = "result/" . $fileName . "/";

// Если директория не существует, то создаем ее
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $result .= '<div class="h1">Результаты:</div>';

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
                    $audioName = $selectVoice . "_" . $selectEmotion;
                    $fullFilePath = "/yandex/" . $filePath;
                    file_put_contents($filePath, $response);
                    $result .= '<div class="my-4">';
                    $result .= '<audio controls>';
                    $result .= '<source src="' . $fullFilePath . '" type="audio/mpeg">';
                    $result .= 'Ваш браузер не поддерживает аудио-элемент.';
                    $result .= '</audio>';
                    $result .= '<a href="' . $fullFilePath . '" class="d-block mt-3" target="_blank" download="'.$audioName.'.mp3">Скачать ' . $audioName . '</a>';
                    $result .= '</div>';
                    $result .= '<hr>';
                }

                curl_close($ch);
            }
        }
    }

    $result .= '<a href="/yandex/" class="h2">Создать новое аудио!</a>';

    return $result;
}

function generate_token()
{
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
    return $token;
}

function mm($mixed, $showAll = false, $collapse = null, $bPrint = true, $position = 'relative')
{
    global $USER;

    static $arCountFuncCall = 0;
    static $arCountFuncCallWithTitleKey = [];

    $arCountFuncCall++;

    $bCollapse = false;

    if ($collapse !== null) {
        $bCollapse = true;
        if (is_string($collapse) && strlen($collapse) > 0) {
            $titleKey = $collapse . '#' . ($arCountFuncCallWithTitleKey[$collapse] ??= 0);
            $arCountFuncCallWithTitleKey[$collapse]++;

            $elemTitle = $titleKey;
            $elemId = rand(1, 500) . $titleKey;
        } else {
            $elemTitle = "dData#{$arCountFuncCall}";
            $elemId = rand(1, 500) . $arCountFuncCall;
        }

        $elemId = str_replace(["'", '"'], '_', $elemId);
    }

    if (!$bPrint) {
        ob_start();
    }

    if ($bCollapse) {
        ?>
        <a href="javascript:void(0)"
           style="position: <?= $position ?>; background: white; border: 1px dotted #5A82CE; padding: 1px 30px; color:#333 "
           onclick="document.getElementById('<?= $elemId ?>').style.display = (document.getElementById('<?= $elemId ?>').style.display == 'none') ? 'block' : 'none'">
            <?= $elemTitle ?>
        </a>
        <div id="<?= $elemId ?>" style="text-align: left; opacity:1 !important; display:none; background-color: #b1cdef; position: absolute; z-index: 10000;">
        <?php
    }

    $content = htmlspecialchars(print_r($mixed, true));
    ?>
    <pre style="text-align: left; color:#222; font-size:13px;"><?= $content ?></pre>
    <?php

    if ($bCollapse) {
        ?>
        </div>
        <?php
    }

    if (!$bPrint) {
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}