<!DOCTYPE html>
<html>

<head>
    <title>Thu âm âm thanh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/toast/jquery.toast.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/simplebar/simplebar.css">
    <link rel="stylesheet" href="/assets/default/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
        }

        .record-button {
            padding: 10px;
            font-size: 24px;
            border-radius: 99%;
            background-color: #4CAF50;
            width: 5%;
            height: 30%;
            color: white;
            cursor: pointer;
            border: none;
        }

        .record-button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="">
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-6">
                    <h2>Thời Gian</h2>
                    <div class="progress">
                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h2>Bộ Đếm Thời Gian</h2>
                    <div id="timer">10:00</div>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 mt-50">
            <div class="col-8 ">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <button class="btn btn-primary ">Previous sentence</button>
                        <h4>PART 1 INTRODUCTION AND INTERVIEW</h4>
                        <button class="btn btn-primary ">Next sentence</button>

                    </div>

                    <div class="card-body">
                        <div class="text-center">
                            <h2 class="text-primary">IT’S THE END OF PART 1</h2>
                            <p>You can review your part 1 recording by clicking the Play button below</p>
                        </div>
                        <div class='d-flex align-items-center justify-content-center'>
                            <button id="recordButton" class="record-button"><i class="fas fa-microphone"></i></button>
                            <audio id="audioPlayer" class="border-0" controls></audio>
                        </div>
                        <div class="text-center mt-20">
                            <p class="part-caption__content" data-drupal-selector="end-part-desc">You can click <strong>Next Part</strong> to continue Part 2 <br>Or <strong>Reset this part</strong> to record again</p>
                            <div class="part-caption__note">Note: your recording will not be saved when reset</div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-4 ">
                <h5 class="text-primary text-center">Hướng dẫn</h5>
            </div>

        </div>
        <script>
            let mediaRecorder;
            let chunks = [];
            let isRecording = false;
            let timerInterval;

            const startRecording = async () => {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        audio: true
                    });
                    mediaRecorder = new MediaRecorder(stream);

                    mediaRecorder.addEventListener('dataavailable', (e) => {
                        chunks.push(e.data);
                    });

                    mediaRecorder.addEventListener('stop', () => {
                        const audioBlob = new Blob(chunks, {
                            type: 'audio/webm'
                        });
                        chunks = [];

                        const audioPlayer = document.getElementById('audioPlayer');
                        audioPlayer.src = URL.createObjectURL(audioBlob);
                    });

                    mediaRecorder.start();
                    isRecording = true;
                    updateButton();
                    startTimer(60 * 10, document.getElementById('timer'), document.getElementById('progress-bar'));
                } catch (error) {
                    console.error('Lỗi khi truy cập microphone:', error);
                }
            };

            const stopRecording = () => {
                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                    isRecording = false;
                    updateButton();
                }
            };

            const updateButton = () => {
                const recordButton = document.getElementById('recordButton');
                recordButton.innerHTML = isRecording ? '<i class="fas fa-stop"></i>' : '<i class="fas fa-microphone"></i>';
            };

            const startTimer = (duration, display, progressBar) => {
                let timer = duration;
                timerInterval = setInterval(function() {
                    let minutes = parseInt(timer / 60, 10);
                    let seconds = parseInt(timer % 60, 10);

                    minutes = minutes < 10 ? "0" + minutes : minutes;
                    seconds = seconds < 10 ? "0" + seconds : seconds;

                    display.textContent = minutes + ":" + seconds;

                    let progress = (duration - timer) / duration * 100;
                    progressBar.style.width = progress + "%";

                    if (--timer < 0) {
                        clearInterval(timerInterval);
                        timer = 0;
                    }
                }, 1000);
            };

            const recordButton = document.getElementById('recordButton');
            recordButton.addEventListener('click', () => {
                if (isRecording) {
                    stopRecording();
                } else {
                    startRecording();
                }
            });
        </script>
</body>

</html>