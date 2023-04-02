<?
require_once "func.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Моя форма</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body>
<div class="container-fluid container-xl">
    <?
    session_start();
    $showForm = true;
    ?>
    <? if (isset($_POST['token']) && isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token']) { ?>
        <? if (isset($_POST['secret']) && $_POST['secret'] == '100грамм') { ?>
            <? if (isset($_POST['MESSAGE'])) { ?>
                <?
                $result = TextToSpeech(htmlspecialchars($_POST['MESSAGE']));
                echo $result;
                $showForm = false;
                // Удаляем токен из сессии, чтобы он не мог быть использован повторно
                unset($_SESSION['token']);
                ?>
            <? } else { ?>
                <p>Текст для генерации не указан!</p>
                <? $showForm = true; ?>
            <? } ?>
        <? } else { ?>
            <p>Секретное слово указано не верно!</p>
            <? $showForm = true; ?>
        <? } ?>
    <? } ?>
    <? if ($showForm) { ?>
        <form method="post" enctype="multipart/form-data" action="">
            <div class="mb-3">
                <div>
                    <label for="MESSAGE" class="form-label">Текст слов:</label>
                </div>
                <textarea class="form-control" id="MESSAGE" name="MESSAGE" rows="4" cols="50"></textarea>
            </div>
            <div class="mb-3">
                <div>
                    <label for="secret" class="form-label">Секретное слово:</label>
                </div>
                <input type="text" class="form-control" id="secret" name="secret">
            </div>
            <button type="submit" class="btn btn-primary">Отправить</button>
            <input type="hidden" name="token" value="<?= generate_token(); ?>">
            <input type="hidden" name="LINK" value="">
        </form>

        <div class="alert alert-info d-none" role="alert">
            Ожидайте, идёт генерация аудио-файлов...&nbsp;<span id="countdown"></span>
        </div>
    <? } ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const countdownEl = document.getElementById("countdown");
        const alertEl = document.querySelector(".alert");

        $('button').click(function() {
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
    });
</script>
</body>
</html>
