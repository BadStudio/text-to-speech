<?

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

$keyFilePath = 'keyfile.json'; //путь до вашего API ключа

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $keyFilePath);

$textToSpeechClient = new TextToSpeechClient();

$input = new SynthesisInput();
$input->setSsml("Всем привет, это команда <phoneme alphabet=\"x-sampa\">Бэд студио</phoneme>! Сегодня мы вам покажем свою новую работу!");

$voice = new VoiceSelectionParams();
$voice->setLanguageCode('ru-RU');

//$voiceName = "Standard-B";
//$voiceName = "Standard-D";
//$voiceName = "Wavenet-B";
$voiceName = "Wavenet-D";
$voice->setName('ru-RU-' . $voiceName);
$voice->setSsmlGender(SsmlVoiceGender::MALE);

$audioConfig = new AudioConfig();
$audioConfig->setAudioEncoding(AudioEncoding::MP3);

$resp = $textToSpeechClient->synthesizeSpeech($input, $voice, $audioConfig);

// Путь к директории, куда будем сохранять файлы
$fileName = $voiceName;
$dirPath = "result/" . $fileName . "/";

// Если директория не существует, то создаем ее
if (!file_exists($dirPath)) {
    mkdir($dirPath, 0777, true);
}

file_put_contents($dirPath.'/result.mp3', $resp->getAudioContent());


