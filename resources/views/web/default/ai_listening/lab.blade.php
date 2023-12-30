@extends(getTemplate() . '.layouts.app')
<div class="d-flex">
    <div style="height: 700px; width:100%;" class="col-8">
        {{-- <iframe width="560" height="315" src="{{ $videoEmbedUrl }}" frameborder="0" allowfullscreen></iframe> --> --}}
        {!! $embedCode !!}
    </div>

    <section class="test-panel scroll container col-4 " style="overflow-y: hidden; outline: none; display: block;"
        tabindex="5">
        <div class="test-panel__item">
            <div class="test-panel__question">
                <div class="test-panel__question-desc">
                    <div
                        class="field field--name-field-block-description field--type-text-long field--label-hidden field--item">
                        <p><em>Choose the correct letter, <strong>A</strong>, <strong>B</strong>, <strong>С</strong> or
                                <strong>D</strong>.</em></p>
                    </div>
                </div>
            </div>


            <div class="overflow-auto " style="height: 600px;">
                @foreach ($body['form'] as $item => $value)
                    <div class="test-panel__question-sm-group" data-num="{{ $item + 1 }}" data-q_type="6">
                        <div class="test-panel__question-sm-title fw-bold"><strong>{{ $item + 1 }}.
                                {{ preg_replace('/^\d+\.\s/', '', $value['question']) }}</strong></div>
                        <div class="test-panel__answer" data-question-item="{{ $item + 1 }}">
                            @foreach ($value['choices'] as $choice => $option)
                                <div
                                    class="test-panel__answer-item d-flex justify-content-space-between align-items-center">
                                    <label class="iot-radio">
                                        <input type="radio" class="radio-iot iot-lr-question"
                                            name="q-{{ $item + 1 }}" data-num="{{ $item + 1 }}"
                                            value="{{ $choice }}"
                                            id="radio-{{ $item + 1 }}-{{ $choice }}">
                                        {{ $choice }}. {{ $option }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <!-- Repeat the above code block as needed -->
            </div>
        </div>
        <button class="bg-primary rounded border-none p-3 mt-10 text-right">end of test</button>
    </section>

</div>
<script>
    const body = {!! json_encode($body) !!};
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelector(".bg-primary").addEventListener("click", function() {

            // console.log(body)
            const selectedRadios = document.querySelectorAll('.radio-iot:checked');

            selectedRadios.forEach(function(selectedRadio, index) {
                const questionNumber = selectedRadio.dataset.num;
                const selectedValue = selectedRadio.value;

                const question = body.form[index].question;
                const correctAnswer = body.form[index].answer;
                if (selectedValue === '') {
                    console.log(
                        `Question ${questionNumber}:${question} - đáp án đúng:${correctAnswer} , ${question} - Bạn k lựa chọn đáp án`
                    );
                } else if (selectedValue === correctAnswer) {
                    console.log(
                        `Question ${questionNumber}:${question} - đáp án bạn chọn:${selectedValue} , đáp án đúng:${correctAnswer} - Correct`
                    );
                } else {
                    console.log(
                        `Question ${questionNumber}: ${question} - đáp án bạn chọn:${selectedValue} , đáp án đúng: ${correctAnswer} - Incorrect`
                    );
                }

            });
        });
    });
</script>
