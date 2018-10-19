@extends('layouts.layout')
@section('crumb')
    @include('layouts.crumb', ['crumbs'=>[['KMS', '/kms/productguide'], ['Notice Center', '/kms/notice'], 'Notice Create']])
@endsection
@section('content')

    <style>
        .form-group label:only-child {
            width: 100%;
            margin-bottom: 0;
        }

        .form-group label .form-control {
            margin-top: 5px;
        }
    </style>

    @include('frank.common')

    <h1 class="page-title font-red-intense"> Single Import
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <form method="post" class="row">
                <div class="col-lg-4">

                    <div class="form-group">
                        <label>
                            Item Group
                            <input required autocomplete="off" class="xform-autotrim form-control" placeholder="Item Group" name="item_group" id="item_group"/>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            Brand
                            <input required autocomplete="off" class="xform-autotrim form-control" placeholder="Brand" name="brand" id="brand"/>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            Item Model
                            <input required autocomplete="off" class="xform-autotrim form-control" placeholder="Item Model" name="item_model" id="item_model"/>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            Video Type
                            <select required autocomplete="off" class="form-control" name="type">
                                @foreach($types as $type)
                                    <option value="{!! $type !!}">{!! $type !!}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <br/>{!! csrf_field() !!}

                    <button type="submit" class="btn btn-primary">Submit</button>

                </div>

                <div class="col-lg-4">

                    <div class="form-group">
                        <label>
                            Video Link
                            <input required pattern=".*\S+.*" autocomplete="off" class="xform-autotrim form-control" placeholder="Link" name="link" title="This field is required."/>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            Note
                            <input autocomplete="off" class="xform-autotrim form-control" placeholder="Note" name="note"/>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            Video Description
                            <textarea autocomplete="off" placeholder="Description" name="descr" class="form-control" style="min-height:7em"></textarea>
                        </label>
                    </div>

                </div>

            </form>
        </div>
    </div>


    <h1 class="page-title font-red-intense"> Batch Import
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>
                        Import by Excel Format
                        <input required autocomplete="off" type="file" style="margin-top: 5px;" accept=".xls, .xlsx" name="excelfile"/>
                    </label>
                    <p class="help-block">Fill in the Excel <a href="/kms/product_video_import.xlsx">template.xlsx</a> and upload it here.</p>
                </div>
                <br/>{!! csrf_field() !!}
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <script>

        new LinkageInput([item_group, brand, item_model], @json($itemGroupBrandModels))

    </script>

@endsection
