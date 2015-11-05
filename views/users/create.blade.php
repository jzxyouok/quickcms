@extends('backend::layouts.master')
@section('content')
    <div id="content">
        <section id="widget-grid" class="">
            <div class="row">
                <article class="col-sm-12 col-md-12 col-lg-6">
                    <div class="jarviswidget" id="wid-id-4" data-widget-editbutton="false" data-widget-custombutton="false">
                        <header>
                            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                            <h2>用户管理 </h2>
                        </header>
                        <div>
                            <div class="jarviswidget-editbox">
                            </div>
                            <div class="widget-body no-padding">
                                <form action="{{ route('admin.users.store') }}" method="post" id="smart-form-register" class="smart-form">
                                    {!! csrf_field() !!}
                                    <header>
                                        编辑用户
                                    </header>
                                    <fieldset>
                                        <section>
                                            <label class="input"> <i class="icon-append fa fa-envelope-o"></i>
                                                <input type="email" name="email" placeholder="Email address">
                                                <b class="tooltip tooltip-bottom-right">Needed to verify your account</b> </label>
                                        </section>
                                        <section>
                                            <label class="input"> <i class="icon-append fa fa-lock"></i>
                                                <input type="password" name="password" placeholder="Password" id="password">
                                                <b class="tooltip tooltip-bottom-right">Don't forget your password</b> </label>
                                        </section>
                                        <section>
                                            <label class="input"> <i class="icon-append fa fa-lock"></i>
                                                <input type="password" name="passwordConfirm" placeholder="Confirm password">
                                                <b class="tooltip tooltip-bottom-right">Don't forget your password</b> </label>
                                        </section>
                                        <section>
                                            <label class="input">
                                                <input type="text" name="name" placeholder="name">
                                            </label>
                                        </section>
                                        <section>
                                            <label class="label">选择角色</label>
                                            <div class="row">
                                                <div class="col col-6">
                                                    <select name="role_id" class="select2">
                                                        @foreach($roles as $role)
                                                            <option value="{{{$role->id}}}">{{{$role->display_name}}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </section>
                                    </fieldset>
                                    <footer>
                                        <button type="submit" class="btn btn-primary">
                                            保存
                                        </button>
                                        <a href="/admin/users" class="btn btn-primary">
                                            返回
                                        </a>
                                    </footer>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </div>
@endsection

@section('script')
    <script src="{{ asset('backend/js/plugin/select2/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var $registerForm = $("#smart-form-register").validate({
                // Rules for form validation
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 3,
                        maxlength: 20
                    },

                    // Messages for form validation
                    messages: {
                        email: {
                            required: 'Please enter your email address',
                            email: 'Please enter a VALID email address'
                        },
                        password: {
                            required: 'Please enter your password'
                        },
                        passwordConfirm: {
                            required: 'Please enter your password one more time',
                            equalTo: 'Please enter the same password as above'
                        },
                        name: {
                            required: 'Please select your name'
                        }
                    },

                    // Do not change code below
                    errorPlacement: function (error, element) {
                        error.insertAfter(element.parent());
                    }
                }
            });
        });
    </script>
@endsection