@extends('layouts.layout')
@section('label', 'Setting Amazon Seller Accounts')
@section('content')
    <h1 class="page-title font-red-intense"> Amazon Seller Accounts
        <small>Configure your Amazon account, email, and proxy email.</small>
    </h1>


    <div class="row"><div class="col-md-8">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-settings font-dark"></i>
                    <span class="caption-subject font-dark sbold uppercase">Seller Account Form</span>
                </div>
            </div>
            <div class="portlet-body form">
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <form role="form" action="{{ url('account/'.$seller_account['id']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <input type="hidden" name="id" value="{{$seller_account['id']}}" />
                    <div class="form-body">
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-group ">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" value="{{$seller_account['account_email']}}" placeholder="Email Address (Important: Use address registered in Seller Central or emails won't send.)" name="account_email" id="account_email" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Account Name</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="account_name" id="account_name" value="{{$seller_account['account_name']}}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>BG</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="bg"  value="{{$seller_account['bg']}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>BU</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="bu"  value="{{$seller_account['bu']}}">
                            </div>
                        </div>
						
						<div class="form-group">
                            <label>Account Level</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select class="form-control form-filter input-sm" name="level">
									@foreach (getAccountLevel() as $k=>$v)
										<option value="{{$k}}" <?php if($k==$seller_account['level']) echo 'selected';?>>{{$v}}</option>
									@endforeach
								</select>
                            </div>
                        </div>
						<div class="form-group">
                            <label>Account Type</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select class="form-control form-filter input-sm" name="type">
									@foreach (getAccountTypes() as $type)
										<option value="{{$type}}" <?php if($type==$seller_account['type']) echo 'selected';?>>{{$type}}</option>
									@endforeach
								</select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Amazon Seller ID</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <input type="text" class="form-control" name="account_sellerid" id="account_sellerid" value="{{$seller_account['account_sellerid']}}" required>
                            </div>
                        </div>

						<div class="form-group">
                            <label>Status</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                <select class="form-control form-filter input-sm" name="status">
									@foreach (\App\Accounts::STATUS as $k=>$v)
										<option value="{{$k}}" <?php if($k==$seller_account['status']) echo 'selected';?>>{{$v}}</option>
									@endforeach
								</select>
                            </div>
                        </div>

						<div class="form-group">
                            <label>Account Signature</label>
                            <div class="input-group">
                                @include('UEditor::head')

                                    <!-- ���ر༭�������� -->
                                    <script id="signature_content" name="signature" type="text/plain">
									<?php echo $seller_account['signature']; ?>
									</script>
                                    <!-- ʵ�����༭�� -->
                                    <script type="text/javascript">
                                        var ue = UE.getEditor('signature_content',{toolbars: [[
            'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',     
            'fontfamily', 'fontsize', '|',
             'touppercase', 'tolowercase', '|',
            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
        ]]});
                                        ue.ready(function() {
                                            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//�˴�Ϊ֧��laravel5 csrf ,����ʵ������޸�,Ŀ�ľ������� _token ֵ.
                                        });
                               		 </script>
                            </div>
                        </div>
						
                        <div class="form-group">
                            <label>Receive Email</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" name="email" id="email" value="{{$seller_account['email']}}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Receive Email Password</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="password" class="form-control" name="password" id="password" value="{{$seller_account['password']}}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Receive Email Imap Host</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="text" class="form-control" name="imap_host" id="imap_host" value="{{$seller_account['imap_host']}}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Receive Email Imap Port</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="text" class="form-control" name="imap_port" id="imap_port" value="{{$seller_account['imap_port']}}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Receive Email Imap SSL</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="text" class="form-control" name="imap_ssl" id="imap_ssl" value="{{$seller_account['imap_ssl']}}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Send Email Smtp Host</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="text" class="form-control" name="smtp_host" id="smtp_host" value="{{$seller_account['smtp_host']}}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Send Email Smtp Port</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="text" class="form-control" name="smtp_port" id="smtp_port" value="{{$seller_account['smtp_port']}}" >
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Send Email Smtp SSL</label>
                            <div class="input-group col-md-6">
                                <span class="input-group-addon">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input type="text" class="form-control" name="smtp_ssl" id="smtp_ssl" value="{{$seller_account['smtp_ssl']}}">
                            </div>
                        </div>

                            <!--
                            <label>Amazon AWS Access KeyId</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Email Address (Important: Use address registered in Seller Central or emails won't send.)">
                            </div>

                            <label>Amazon Secret Key</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Email Address (Important: Use address registered in Seller Central or emails won't send.)">
                            </div>
                            -->

                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button type="submit" class="btn blue">Submit</button>
                                <button type="reset" class="btn grey-salsa btn-outline">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="portlet light bordered" id="blockui_sample_1_portlet_body">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-bubble font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp sbold">How to use it?</span>
                </div>
            </div>
            <div class="portlet-body">
                The Email Address and Seller ID  Must  Use address registered in Seller Central!
                <p><p>In order to protect your account information, you can use forwarding or collection to forward the mail to another receiving mailbox to authorize us to receive mail.
            </div>
        </div>

    </div>

    </div>


@endsection
