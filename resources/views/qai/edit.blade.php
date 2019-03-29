@extends('layouts.layout')
@section('label', 'Edit Qa')
@section('content')
    <script>

        {{--$(function() {--}}
            {{--var knowledge_type = '<?php echo $qa['knowledge_type']?>';--}}
            {{--var k_type = '<?php echo $qa['for_product2']?>';--}}

            {{--if(knowledge_type == '产品知识'){--}}
                {{--$('.r_type').show();--}}
            {{--}else{--}}
                {{--$('.r_type').hide();--}}
            {{--}--}}

            {{--if(k_type == 'AG-AP'){--}}
                {{--$('.r_product_level').show();--}}
            {{--}else{--}}
                {{--$('.r_product_level').hide();--}}
            {{--}--}}
        {{--});--}}

    </script>
    <h1 class="page-title font-red-intense"> Edit Qa
        <small>Configure your Qa.</small>
    </h1>

    <div class="row"><div class="col-md-12">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-settings font-dark"></i>
                        <span class="caption-subject font-dark sbold uppercase">Qa Form</span>
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
                    <form role="form" action="{{ url('qa/'.$qa['id']) }}" method="POST">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <input type="hidden" name="id" value="{{$qa['id']}}" />
                        <div class="form-body">
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Title</label>
                                    <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-user"></i>
                                    </span>
                                        <input type="text" class="form-control" name="title" id="title" value="{{$qa['title']}}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Details</label>
                                    <div class="input-group">
                                        @include('UEditor::head')

                                                <!-- 加载编辑器的容器 -->
                                        <script id="qa_content" name="description" type="text/plain">
                                            <?php echo $qa['description']; ?>
                                        </script>
                                        <!-- 实例化编辑器 -->
                                        <script type="text/javascript">
                                            var ue = UE.getEditor('qa_content',{toolbars: [[
                                                'fullscreen', 'source', '|', 'undo', 'redo', '|',
                                                'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
                                                'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
                                                'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
                                                'directionalityltr', 'directionalityrtl', 'indent', '|',
                                                'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
                                                'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
                                                'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
                                                'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
                                                'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
                                                'print', 'preview', 'searchreplace', 'drafts', 'help'
                                            ]]});
                                            ue.ready(function() {
                                                ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
                                            });
                                        </script>
                                    </div>
                                </div>



                                <div class="form-group">
                                    <label>Details （Chinese）</label>
                                    <div class="input-group">
                                        <!-- 加载编辑器的容器 -->
                                        <script id="dqe_content" name="dqe_content" type="text/plain">
                                            <?php echo $qa['dqe_content']; ?>
                                        </script>
                                        <!-- 实例化编辑器 -->
                                        <script type="text/javascript">
                                            var ue = UE.getEditor('dqe_content',{toolbars: [[
                                                'fullscreen', 'source', '|', 'undo', 'redo', '|',
                                                'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
                                                'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
                                                'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
                                                'directionalityltr', 'directionalityrtl', 'indent', '|',
                                                'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
                                                'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
                                                'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
                                                'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
                                                'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
                                                'print', 'preview', 'searchreplace', 'drafts', 'help'
                                            ]]});
                                            ue.ready(function() {
                                                ue.execCommand('serverparam', '_token', '{{ csrf_token() }}');//此处为支持laravel5 csrf ,根据实际情况修改,目的就是设置 _token 值.
                                            });
                                        </script>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group">
                                    <label>Knowledge Type</label>
                                    <div class="input-group ">
                            <span class="input-group-addon">
                                <i class="fa fa-bookmark"></i>
                            </span>
                                        <select class="form-control" name="knowledge_type" id="knowledge_type" onchange="r_type();" required>
                                            <option>None</option>

                                            <?php
                                            echo procHtml($tree,0, $qa['knowledge_type']);
                                            //foreach($tree as $key=>$val){
                                            ?>

                                            <?php
                                            //}
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group r_type">
                                    <label>For Product</label>
                                    <div class="epoint_selectList form-inline">
                                        <select class="for_product1 form-control" name="for_product1">
                                            <option value="ALL">ALL</option>
                                            @foreach ($groups as $user_id=>$user_name)
                                                <option value="{{$user_name}}" <?php if($user_name==$qa['for_product1']) echo 'selected';?>>{{$user_name}}</option>
                                            @endforeach
                                        </select>
                                        <select class="for_product2 form-control" name="for_product2" id="for_product2" onchange="r_product_level();">
                                            <option value="ALL">ALL</option>
                                            @foreach($for_product2 as $key=>$val)
                                                <option value="{{$val}}" <?php if($val==$qa['for_product2']) echo 'selected';?>>{{$val}}</option>
                                            @endforeach
                                        </select>
                                        <select class="for_product3 form-control" name="for_product3">
                                            <option value="ALL">ALL</option>
                                            @foreach($for_product3 as $key=>$val)
                                                <option value="{{$val}}" <?php if($val==$qa['for_product3']) echo 'selected';?>>{{$val}}</option>
                                            @endforeach
                                        </select>
                                        <select class="for_product4 form-control" name="for_product4">
                                            <option value="ALL">ALL</option>
                                            @foreach($for_product4 as $key=>$val)
                                                <option value="{{$val}}" <?php if($val==$qa['for_product4']) echo 'selected';?>>{{$val}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group r_product_level">
                                    <label>For Question</label>
                                    <div class="input-group ">
                                    <span class="input-group-addon">
                                        <i class="fa fa-bookmark"></i>
                                    </span>
                                        <select class="form-control form-filter input-sm" name="for_question">
                                            <option>None</option>
                                            <?php
                                            foreach($trees as $key=>$val){
                                            ?>
                                            <option value="<?=$val['category_name'];?>" <?php if($val['category_name']==$qa['for_question']) echo 'selected';?>><?=$val['category_name'];?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group r_type">
                                    <label>Similar Question</label>
                                    <div style="clear: both;"></div>
                                    <div class="input-group col-md-10 similar_question" style="float: left;">
                                        <?php
                                            $similar_question = json_decode($qa['similar_question'], true);
                                            if(!empty($similar_question)){
                                                foreach($similar_question as $key=>$val){
                                        ?>
                                            <div class="input-group col-md-12">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <input type="text" class="form-control" placeholder="Similar Question" name="similar_question[]" id="similar_question" value="<?=$val;?>" />
                                            </div>
                                        <?php
                                                }
                                            }else{
                                        ?>
                                            <div class="input-group col-md-12">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-bookmark"></i>
                                                </span>
                                                <input type="text" class="form-control" placeholder="Similar Question" name="similar_question[]" id="similar_question" />
                                            </div>
                                        <?php
                                            }
                                        ?>

                                    </div>
                                    <div class="col-md-2" style="float: right; margin-top: 9px;"> <i class="fa fa-plus-square add_similar_question" style=" font-size: 36px;cursor: pointer;"></i><!--<i class="fa fa-minus-square del_similar_question" style=" font-size: 36px;cursor: pointer;"></i>--> </div>
                                    <div style="clear: both;"></div>
                                </div>

                                <div class="form-group">
                                    <label>Related  Knowledge</label>
                                    <div class="input-group ">
                                    <span class="input-group-addon">
                                        <i class="fa fa-bookmark"></i>
                                    </span>
                                        <select class="mt-multiselect btn btn-default input-sm form-control form-filter" multiple="multiple" data-label="left" data-width="100%" data-filter="true" data-action-onchange="true" name="related_knowledge[]" id="related_knowledge[]">

                                            @foreach ($qas as $key=>$val)
                                                <option value="{{$val['id']}}" <?php if(in_array($val['id'],explode(';',$qa['related_knowledge']))) echo 'selected';?>>{{$val['title']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="input-group col-md-6">
                                    <span class="input-group-addon">
                                        <i class="fa fa-user"></i>
                                    </span>
                                        <select class="form-control form-filter input-sm" name="confirm">
                                            <option value="0" <?php if(0==$qa['confirm']) echo 'selected';?>>Pending</option>
                                            <option value="1" <?php if(1==$qa['confirm']) echo 'selected';?>>Active</option>
                                            <option value="2" <?php if(2==$qa['confirm']) echo 'selected';?>>Invaild</option>

                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12" style="text-align: center;">
                                    <button type="submit" class="btn blue">Submit</button>
                                    <button type="reset" class="btn grey-salsa btn-outline">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div>
    <script type="text/javascript">
//        function r_type(){
//            var k_type = $("#knowledge_type").val();
//            if(k_type == '产品知识'){
//                $('.r_type').show();
//            }else{
//                $('.r_type').hide();
//            }
//        }
//        function r_product_level(){
//            var k_type = $("#for_product2").val();
//            if(k_type == 'AG-AP'){
//                $('.r_product_level').show();
//            }else{
//                $('.r_product_level').hide();
//            }
//        }

        $('.add_similar_question').on('click', function(){
            var html = '<div class="input-group col-md-12"><span class="input-group-addon"><i class="fa fa-bookmark"></i></span><input type="text" class="form-control" placeholder="Similar Question" name="similar_question[]" id="similar_question" /></div>';
            $('.similar_question').append(html);
        });
    </script>
    <?php
    function procHtml($tree,$level = 0,$id)
    {
        $html = '';
        foreach($tree as $key=>$val)
        {
            if($val['category_pid'] == '') {
                $html .= '<option value="'.$val['id'].'">'.$val['category_name'].' </option>';
            }else{
                $flg = str_repeat('|----',$level);
                $selected = ($val['id']==$id) ? 'selected' : '';
                $html .= '<option value="'.$val['id'].'" '.$selected.'>'.$flg.$val['category_name'];
                $html .= procHtml($val['category_pid'],$level+1,$id);
                $html = $html."</option>";
            }
        }
        return $html;
    }
    ?>
@endsection
