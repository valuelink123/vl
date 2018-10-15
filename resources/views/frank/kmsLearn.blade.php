@extends('layouts.layout')
@section('label', 'Knowledge Manage')
@section('content')

    @include('frank.common')

    <h1 class="page-title font-red-intense"> Learn Center
        <small></small>
    </h1>

    <div class="portlet light bordered">
        <div class="portlet-body">

            <br/>

            {{--搜索栏--}}
            <div class="row">
                <div class="col-lg-3">
                    <div class="input-group">
                        <span class="input-group-addon">Item Group</span>
                        <input type="text" class="xform-autotrim form-control" data-init-by-query="ands.item_group" placeholder="Item Group..." id="item_group" autocomplete="off"/>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="input-group">
                        <span class="input-group-addon">Item Model</span>
                        <input type="text" class="xform-autotrim form-control" data-init-by-query="ands.item_model" placeholder="Item Model..." id="item_model" autocomplete="off"/>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="input-group">
                        <input type="text" class="xform-autotrim form-control" data-init-by-query="value" placeholder="Fuzzy search..." id="fuzzysearch" autocomplete="off"/>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" id="dosearch">Search!</button>
                        </span>
                    </div>
                </div>
            </div>

            <br/><br/>

            {{--文章列表--}}
            <div class="row">
                <div class="col-lg-9">
                    <div class="list-group" id="thelist"></div>
                    <script type="text/template">
                        `
                        <div class="list-group-item">
                            <h4 class="list-group-item-heading">
                                <a href="#"><b>${row['title']}</b></a>
                                <a href="#" style="float:right">view more</a>
                                <b style="clear:both;"></b>
                            </h4>
                            <p class="list-group-item-text">${row['content']}</p>
                            <br/>
                        </div>
                        `
                    </script>
                </div>
            </div>

            <br/>

            {{--分页信息--}}
            <div class="row">
                <div class="col-lg-4">
                    <span id="paginfo" data-tpl="`Showing ${start + 1} to ${Math.min(start + length, total)} of ${total} entries`"></span>
                </div>
                <div class="col-lg-5" style="text-align:right">
                    <div class="pagination-panel" id="thepagination">
                        Page
                        <a href="#" class="btn btn-sm default prev"><i class="fa fa-angle-left"></i></a>
                        <input type="text" class="pagination-panel-input form-control input-sm input-inline input-mini" maxlenght="5" style="text-align:center; margin: 0 5px;"/>
                        <a href="#" class="btn btn-sm default next"><i class="fa fa-angle-right"></i></a>
                        of <span class="pagination-panel-total"></span>
                    </div>
                </div>
            </div>

            <br/><br/>

        </div>
    </div>

    <script>

        new LinkageInput([item_group, item_model], @json($itemGroupModels))

        async function loadData(page = 1) {

            if (isNaN(page) || page < 1) page = 1

            let length = 10
            let start = (page - 1) * length

            // 请求数据

            let search = {
                page,
                ands: {
                    item_group: item_group.value,
                    item_model: item_model.value
                },
                value: fuzzysearch.value
            }

            let {rows, total} = await new Promise((resolve, reject) => {
                $.ajax({
                    data: {start, length, search, order: [{field: 'updated_at', dir: 'desc'}]},
                    method: 'POST',
                    url: '/kms/learn/get',
                    success(data) {
                        resolve(data)
                    },
                    error(xhr, status, errmsg) {
                        reject(new Error(errmsg))
                    }
                })
            })

            if (rows.length <= 0 && total > 0) {
                // 页码超出范围的情况
                return loadData(Math.ceil(total / length))
            }

            history.replaceState(null, null, '?' + objectToQueryString(search))


            // 渲染列表数据

            let tpl = $(thelist).next().html()
            let $thelist = $(thelist)

            $thelist.empty()

            for (let row of rows) {
                let html = eval(tpl)
                $thelist.append(html)
            }


            if (rows.length <= 0 && total <= 0) {
                page = 0
                start = -1
                $(thelist).html('<div style="text-align:center; font-weight:bold; padding:7em 0;" class="list-group-item">Nothing to Show.</div>')
            }


            // 渲染分页信息

            $(thepagination).children('input').val(page)

            $(thepagination).children('.pagination-panel-total').html(Math.ceil(total / length))

            let $paginfo = $(paginfo)

            $paginfo.html(eval($paginfo.data('tpl')))
        }

        function init() {

            let query = queryStringToObject()

            XFormHelper.initByQuery('.form-control[data-init-by-query]')


            $(thepagination).on('click', '.prev', function (e) {
                e.preventDefault()
                loadData($(thepagination).children('input').val() - 1)
            })

            $(thepagination).on('click', '.next', function (e) {
                e.preventDefault()
                loadData($(thepagination).children('input').val() - -1)
            })

            $(thepagination).on('change', 'input', function (e) {
                loadData(e.target.value)
            })

            $([item_group, item_model]).change(loadData)
            bindDelayEvents(fuzzysearch, 'change keyup paste', loadData)

            $(dosearch).click(loadData)

            loadData(query.page)
        }

        $(init)

    </script>

@endsection
