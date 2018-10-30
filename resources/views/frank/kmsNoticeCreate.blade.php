@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>[['KMS', '/kms/productguide'], ['Notice Center', '/kms/notice'], 'Add New']])
@endsection
@section('content')

    <style>
        .tag-editor {
            line-height: 26px !important;
            border: none !important;
        }
    </style>

    @include('UEditor::head')
    @include('frank.common')
    @include('frank.tagEditor')

    <h1 class="page-title font-red-intense"> New Notice
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <form method="post" class="row" id="theform">

                <div class="col-md-2">
                    <div class="form-group">
                        <label>
                            Item Group
                            <input required autocomplete="off" class="xform-autotrim form-control" placeholder="Item Group" name="item_group" id="item_group"/>
                        </label>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label>
                            Brand
                            <input required autocomplete="off" class="xform-autotrim form-control" placeholder="Brand" name="brand" id="brand"/>
                        </label>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label>
                            Item Model
                            <input required autocomplete="off" class="xform-autotrim form-control" placeholder="Item Model" name="item_model" id="item_model"/>
                        </label>
                    </div>
                </div>

            </form>

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>
                            <b>Title</b>
                            <textarea required form="theform" autocomplete="off" placeholder="Notice Title ..." name="title" class="xform-autotrim form-control"
                                      style="height:34px"></textarea>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-8">
                    <div class="form-group">
                        <label><b>Content</b></label>
                        <script id="bdeditor" type="text/plain"></script>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-8">
                    <div class="form-group">
                        <label>
                            Tags
                            <span class="form-control" style="padding:0; height:auto;">
                                <textarea form="theform" name="tags" id="tags"></textarea>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-8" id="submit-result-msg"></div>
                <br/><br/>
                <div class="col-xs-8">
                    <input form="theform" type="hidden" name="_token" value="{!! $csrf_token = csrf_token() !!}"/>
                    <button form="theform" type="submit" class="btn btn-primary" style="padding:.5em 3em;">Submit</button>
                </div>
            </div>

        </div>
    </div>

    <script>

        new LinkageInput([item_group, brand, item_model], @json($itemGroupBrandModels))

        $('#tags').tagEditor({
            autocomplete: {
                delay: 0,
                source: @json($tags),
                position: {collision: 'flip'},
            },
            forceLowercase: false,
            placeholder: 'Enter tags ...'
        });


        let ue = UE.getEditor('bdeditor', {
            topOffset: 60,
            initialFrameWidth: "100%",
        })

        ue.ready(function () {
            ue.execCommand('serverparam', '_token', '{!! $csrf_token !!}')
        })

        let theid = ''

        $(theform).submit(e => {

            let formData = rows2object($(theform).serializeArray(), 'name', 'value')

            formData.content = ue.getContent()
            formData.id = theid

            new Promise((resolve, reject) => {

                $.post('', formData).success(res => {

                    if (res[0] === false) {
                        reject(res[1])
                    } else {
                        theid = res[0]
                        resolve(res[1])
                    }
                }).error((xhr, status, msg) => {
                    reject(msg)
                })

                $('#submit-result-msg').css('min-height', '71px').empty()

            }).then(msg => {
                // toastr.success('todo 所谓通知')
                $('#submit-result-msg').html(`<div class="alert alert-success"><strong>Success !</strong> ${msg}</div>`)
            }).catch(msg => {
                $('#submit-result-msg').html(`<div class="alert alert-danger"><strong>Error !</strong> ${msg}</div>`)
            })

            return false
        })

    </script>

@endsection
