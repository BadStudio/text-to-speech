<?
require_once "defines.php";
require_once "func.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Моя форма</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>
<body>
<div class="container-fluid container-xl">
    <?
    session_start();
    $showForm = true;
    $scriptRun = false;
    $isLocal = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.local') !== false) ? true : false;
    ?>
    <? if (isset($_POST['token']) && isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token']) { ?>
        <? if ($isLocal) { ?>
            <? $scriptRun = true; ?>
        <? } else { ?>
            <? if (isset($_POST['secret']) && $_POST['secret'] == '100грамм') { ?>

            <? } else { ?>
                <p>Секретное слово указано не верно!</p>
                <? $showForm = true; ?>
            <? } ?>
        <? } ?>
    <? } ?>
    <? if ($scriptRun) { ?>
        <? if (isset($_POST['MESSAGE']) && !empty($_POST['MESSAGE'])) { ?>
            <?
            if (isset($_POST['VOICE']) && empty($_POST['VOICE'])) {
                $_POST['VOICE'] = 'filipp_neutral';
            }
            if (isset($_POST['SPEED']) && empty($_POST['SPEED'])) {
                $_POST['SPEED'] = '1';
            }
            $result = TextToSpeech($_POST['MESSAGE'], $_POST['VOICE'], $_POST['SPEED']);
            echo $result;
            $showForm = false;
            // Удаляем токен из сессии, чтобы он не мог быть использован повторно
            unset($_SESSION['token']);
            ?>
        <? } else { ?>
            <p>Текст для генерации не указан!</p>
            <? $showForm = true; ?>
        <? } ?>
    <? } ?>
    <? if ($showForm) { ?>
        <h1>Создать голос на основе текста</h1>
        <form method="post" enctype="multipart/form-data" action="">
            <div class="mb-3">
                <div>
                    <label for="MESSAGE" class="form-label">Текст:</label>
                </div>
                <textarea class="form-control" id="MESSAGE" name="MESSAGE" rows="4" cols="50"></textarea>
            </div>
            <div class="mb-3">
                <div>
                    <label for="VOICE" class="form-label">Выберите голос</label>
                </div>
                <select name="VOICE" class="form-select" id="VOICE">
                    <? foreach ($arVoices as $arVoice) { ?>
                        <option value="<?= $arVoice['CODE']; ?>"><?= $arVoice['NAME']; ?></option>
                    <? } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="SPEED">Скорость: <span class="js-speed-select"></span></label>
                <div class="js-speed-uiSlider"></div>
                <input type="hidden" name="SPEED" class="js-speed-input" id="SPEED">
            </div>
            <div class="mb-3">
                <div>
                    <label for="secret" class="form-label">Секретное слово:</label>
                </div>
                <input type="text" class="form-control" id="secret" name="secret">
            </div>
            <button type="submit" class="btn btn-primary">Отправить</button>
            <input type="hidden" name="token" value="<?= generate_token(); ?>">
        </form>

        <div class="alert alert-info d-none" role="alert">
            Ожидайте, идёт генерация аудио-файлов...&nbsp;<span id="countdown"></span>
        </div>

        <h2 class="mt-4">Демонстрация голоса</h2>
        <?

        $codeToName = array_combine(
            array_column($arVoices, 'CODE'),
            array_column($arVoices, 'NAME')
        );

        $directory = $_SERVER['DOCUMENT_ROOT'] . "/yandex/demo/";
        $mp3Files = glob($directory . "*.mp3");


        foreach ($mp3Files as $file) {
            $file = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
            $pattern = "/\/yandex\/demo\/(.+)\.mp3/";
            preg_match($pattern, $file, $matches);

            // действия с каждым mp3 файлом
            $result = '<div class="my-4">';
            $result .= '<div class="h3">' . $codeToName[$matches[1]] . '</div>';
            $result .= '<audio controls>';
            $result .= '<source src="' . $file . '" type="audio/mpeg">';
            $result .= 'Ваш браузер не поддерживает аудио-элемент.';
            $result .= '</audio>';
            $result .= '</div>';
            $result .= '<hr>';

            echo $result;
        }

        ?>
    <? } ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"
        integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function () {
        $(function () {
            const countdownEl = document.getElementById("countdown");
            const alertEl = document.querySelector(".alert");

            $('button').click(function () {
                $(this).hide();
                alertEl.classList.remove('d-none');

                let secondsLeft = 30;
                countdownEl.textContent = secondsLeft; // начальное значение отсчета

                const intervalId = setInterval(() => {
                    secondsLeft--;
                    countdownEl.textContent = secondsLeft;
                    if (secondsLeft === 0) {
                        countdownEl.textContent = "надо ещё немного времени :)";
                        clearInterval(intervalId);
                        //alertEl.style.display = "none";
                    }
                }, 1000);
            });
        })

        $(function () {
            let uiSlider = $(".js-speed-uiSlider"),
                input = $(".js-speed-input"),
                select = $(".js-speed-select");
            uiSlider.slider({
                range: "min",
                value: 1.0,
                step: 0.1,
                min: 0.1,
                max: 3.01,
                slide: function (event, ui) {
                    // Обновление значения в скрытом поле формы
                    input.val(ui.value);
                    select.html(ui.value + "x");
                }
            });

            // Начальное значение в скрытом поле формы
            let selectDefault = uiSlider.slider("value");
            input.val(selectDefault);
            select.html(selectDefault + "x");

        })
    });
</script>
</body>
</html>
