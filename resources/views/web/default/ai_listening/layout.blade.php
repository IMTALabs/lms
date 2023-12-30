@extends(getTemplate().'.layouts.app')
@extends('web.default.layouts.app')
<!-- @push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush -->
@section('content')
<style>

</style>
<div class="container d-flex justify-content-space-between align-items-center mb-10">
  <div class="col-9">
    <div class="nav  d-flex justify-content-around mt-10 ">
      <a href="#" class="nav-link rounded border border-secondary text-primary active" onclick="changeTab(event, 'listening')">
        <i class="fas fa-headphones me-1"></i>
        Listening
      </a>
      <a href="#" class="nav-link rounded border border-secondary text-primary" onclick="changeTab(event, 'reading')">
        <i class="fas fa-book me-1"></i>
        Reading
      </a>
      <a href="#" class="nav-link rounded border border-secondary text-primary" onclick="changeTab(event, 'writing')">
        <i class="fas fa-pen me-1"></i>
        Writing
      </a>
      <a href="#" class="nav-link rounded border border-secondary text-primary" onclick="changeTab(event, 'speaking')">
        <i class="fas fa-comments me-1"></i>
        Speaking
      </a>

    </div>

  </div>
  <div class="col-3">
    <select class="form-control" id="skill">
      <option value="listening">Listening</option>
      <option value="reading">Reading</option>
      <option value="writing">Writing</option>
      <option value="speaking">Speaking</option>
    </select>
  </div>
</div>


<div class="container">
  <div id="listening" class="tab-content">
    <h2>Listening Tab Content</h2>
    <form method="GET" action="/lab_listening">
      <div class="form-group row ">
        <div class="col-sm-12 d-flex justify-content-space-between align-items-center">
          <input type="url" class="form-control" name="listen_link" id="inputPassword" placeholder="vui lòng điền link">

        </div>
      </div>
      <div class="text-center">
        <button type="submit" class="btn btn-primary">Gửi</button>
      </div>
    </form>
  </div>

  <div id="reading" class="tab-content container" style="display: none;">
    <h2 class="text-center text-primary">Choose a mode reading</h2>
    <div class="row mt-10 gap-2">
      <div class="col mt-10 ">
        <h3 class="text-center text-secondary">Topic selection available</h3>
        <div class="card mt-20">
          <div class="card-body">
            <div>
              <select class="form-select w-100 h-100 text-primary border rounded border-primary p-10" style="height: 40px;">
                <option selected>Topic selection available</option>
                <option value="1">Toppic1</option>
                <option value="2">Toppic2</option>
                <option value="3">Toppic3</option>
              </select>
            </div>
            <div class="px-50 py-20">
              <button class="btn btn-primary w-100"><a href="/lab_reading">Start</a></button>
            </div>

          </div>
        </div>
      </div>
      <div class="col mt-10">
        <h3 class="text-center text-secondary">Enter your topic</h3>
        <div class="card mt-20">
          <div class="card-body form">
            <div>
              <textarea class="form-control h-auto border border-primary text-primary " placeholder="Type your essay here ......" id="exampleFormControlTextarea1" rows="3"></textarea>
            </div>
            <div class="px-50 py-20">
              <button class="btn btn-primary w-100"><a href="/lab_reading">Start</a></button>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>

  <div id="writing" class="tab-content" style="display: none;">
    <h2 class="text-center text-primary">Choose a mode writing</h2>
    <div class="row mt-10 gap-2">
      <div class="col mt-10 ">
        <h3 class="text-center text-secondary">Topic selection available</h3>
        <div class="card mt-20">
          <div class="card-body">
            <div>
              <select class="form-select w-100 h-100 text-primary border rounded border-primary p-10" style="height: 40px;">
                <option selected>Topic selection available</option>
                <option value="1">Toppic1</option>
                <option value="2">Toppic2</option>
                <option value="3">Toppic3</option>
              </select>
            </div>
            <div class="px-50 py-20">
              <button class="btn btn-primary w-100"><a href="/lab_writing">Start</a></button>
            </div>

          </div>
        </div>
      </div>
      <div class="col mt-10">
        <h3 class="text-center text-secondary">Enter your topic</h3>
        <div class="card mt-20">
          <div class="card-body form">
            <div>
              <input type="text" name="" id="" class=" w-100 h-100 text-primary border rounded border-primary p-10">
            </div>
            <div class="px-50 py-20">
              <button class="btn btn-primary w-100"><a href="/lab_writing">Start</a></button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="speaking" class="tab-content" style="display: none;">
    <h2>Speaking Tab Content</h2>

  </div>
</div>
<script>
  function changeTab(event, tabId) {
    // Prevent default link behavior
    event.preventDefault();

    // Remove 'active' class from all nav links
    let navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
      link.classList.remove('active');
    });

    // Add 'active' class to the clicked nav link
    event.target.classList.add('active');

    // Hide all tab contents
    let tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => {
      tab.style.display = 'none';
    });

    // Display the selected tab content
    let selectedTab = document.getElementById(tabId);
    selectedTab.style.display = 'block';
  }
</script>
@endsection