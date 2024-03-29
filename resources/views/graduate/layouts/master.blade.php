<!DOCTYPE html>
<html lang="{{Str::of(config('app.locale'))->replace('_', '-')}}">
    @include('graduate.layouts.head')
    <body>
    <script type="text/javascript">
        var siteurl = "{{route('portal.home')}}"
        var baseurl = "{{route('graduate.home')}}";
        var adminurl = "{{route('graduate.admin.home')}}";
    </script>
        @include('graduate.layouts.navbar')
        @if(auth('graduate')->check())
        <div class="page-content">
            <div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">
                <div class="sidebar-mobile-toggler text-center">
                    <a href="#" class="sidebar-mobile-main-toggle"><i class="icon-arrow-left8"></i></a>
                    Navigasi
                    <a href="#" class="sidebar-mobile-expand"><i class="icon-screen-full"></i><i class="icon-screen-normal"></i></a>
                </div>
                <div class="sidebar-content">
                    <div class="sidebar-user">
                        <div class="card-body">
                            <div class="media">
                                <div class="mr-2">
                                    <a href="#"><img src="{{asset('storage/master/images/'.$school->value('school_logo'))}}" width="50" height="50"></a>
                                </div>
                                <div class="media-body">
                                    <div class="media-title font-weight-semibold">{{$school->name(false)}}</div>
                                    <div class="font-size-xs opacity-50">
                                        <i class="icon-pin font-size-sm"></i> &nbsp;{{$school->value('school_address')}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('graduate.layouts.mainmenu')
                </div>
            </div>
            @endif
            <div class="content-wrapper">
                @if(auth('graduate')->check())
                @include('graduate.layouts.header')
                <div class="content">
                    @yield('content')
                </div>
                @else
                    <div class="content d-flex justify-content-center align-items-center">
                        @yield('content')
                    </div>
                @endif
                @include('graduate.layouts.footer')
            </div>
        </div>
    @yield('modal')
    </body>
</html>
