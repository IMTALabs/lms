@extends(getTemplate().'.layouts.app')
<div class="d-flex gap-2">
    <div class="col-6 scroll" style="overflow-y: hidden; outline: none; display: block; " tabindex="2">

        <h1 class="text-center text-primary mt-10">You should spend about 20 minutes on this task.</h1>

        <div class=" overflow-auto mt-20 px-20" style="line-height: 1.5; height: 600px;">
            <p class="mt-20">The bar chart below describes some changes about the percentage of people were born in Australia and who were born outside Australia living in urban, rural and town between 1995 and 2010.</p>
            <p>Summarise the information by selecting and reporting the main features and make comparisons where relevant.</p>
            <p>You should write <strong>at least 150 words.</strong></p>
            <img src="https://iotcdn.oss-ap-southeast-1.aliyuncs.com/2023-01/task1_5.png" class="w-100" alt="image_lab">
        </div>
    </div>
    <div class="col-6 scroll" style="overflow-y: hidden; outline: none; display: block; " tabindex="2">
        <div class=" overflow-auto mt-50 px-20" style="line-height: 1.5; height: 600px;">
            <div class="h-75">
                <textarea class="form-control h-100 border border-primary text-primary " placeholder="Type your essay here ......" id="exampleFormControlTextarea1" rows="3"></textarea>
            </div>
            <div class="text-right">
                <button class="btn btn-primary w-50 mt-20"><a href="">End of exam</a></button>
            </div>

        </div>

    </div>
</div>