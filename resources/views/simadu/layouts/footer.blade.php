<div class="navbar navbar-expand-lg navbar-light">
    <div class="text-center d-lg-none w-100">
        <button type="button" class="navbar-toggler dropdown-toggle" data-toggle="collapse" data-target="#navbar-footer">
            <i class="icon-unfold mr-2"></i>
            Footer
        </button>
    </div>
    <div class="navbar-collapse collapse" id="navbar-footer">
        <span class="navbar-text">
            &copy; 2020 <a href="#">{{$setting->value('app_name')}}</a>
            Copyrigth By <a href="#">{{$school->name(false)}}</a>
            Created By <span class="font-weight-semibold font-italic">Limitless</span>
        </span>
        <ul class="navbar-nav ml-lg-auto">
            <li class="nav-item"><a href="#" class="navbar-nav-link" target="_blank"><i class="icon-file-text2 mr-2"></i> Dokumen</a></li>
            <li class="nav-item"><a href="#" class="navbar-nav-link" target="_blank"><i class="icon-help mr-2"></i> Dukungan</a></li>
        </ul>
    </div>
</div>
