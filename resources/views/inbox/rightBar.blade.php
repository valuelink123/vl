<style>
    #email-detail-right-bar .panel-heading {
        display: block;
        cursor: pointer;
        text-decoration: none;
    }

    .blink {
        animation: blinker 1s linear infinite;
    }

    @keyframes blinker {
        50% {
            font-weight: bold;
        }
    }

    .right_bar_div {
        width: 100%;
        border-radius: 5px !important;
        border: 1px solid grey;
        margin-bottom: 25px;
        padding-left: 10px;
        padding-right: 5px;
        box-shadow: 5px 5px 5px #c5c5c5;
    }
    .span_inline_ellipse{
        display: inline-block;
        width:250px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .less_space{
        margin:0px;
        padding:0px;
    }
    .head_title{
        margin-top: 15px;
        margin-bottom: 10px;
    }
</style>

@include('frank.common')
<div class="panel-group" id="email-detail-right-bar">

    <div class="right_bar_div">
        <label class="head_title">Latest conversations</label>
        @foreach($latestConversationList as $data)
        <p class="less_space">
            {{--sendbox的邮件--}}
            @if(isset($data['inbox_id']))
            <span class="span_inline_ellipse"><a href="/send/{{array_get($data,'id')}}" target="_blank"> {{array_get($data,'subject')}}</a></span>
            {{--跟进记录--}}
            @else
            <span class="span_inline_ellipse"><a href="{{'/crm/show?id='.array_get($data,'record_id').'#email-history'}}" target="_blank"> {{array_get($data,'note')}}</a></span>
            @endif
            <span class="pull-right">&nbsp;{{$data['interval']}}</span>
        </p>
        @endforeach
    </div>

    <div class="right_bar_div">
        <label class="head_title">Recent events</label>
        @foreach ($recentEventsList as $data)
        <p class="less_space">
            <span class="span_inline_ellipse">
            @if($data['event_type'] == 'non_ctg')
            Active Warranty for ASIN:
            @elseif($data['event_type'] == 'ctg')
            Apply CTG for ASIN:
            @else
            Apply RSG for ASIN:
            @endif
            <a href="https://{{array_get($data,'marketPlaceSite').'/dp/'.array_get($data,'asin')}}" target="_blank"> {{array_get($data,'asin')}}</a></span>
            <span class="pull-right">&nbsp;{{$data['interval']}}</span>
        </p>
        @endforeach
        <div style="margin-bottom: 10px; text-align:center">
            NON-CTG
            @if($recentEventsNumbers['times_non_ctg'] > 0)
            <a href="/nonctg?value={{$client_email}}" target="_blank">
            @endif
            <input type='button' value="{{$recentEventsNumbers['times_non_ctg']}}" size="2" readonly style="margin-right:3px" />
            @if($recentEventsNumbers['times_non_ctg'] > 0)
            </a>
            @endif

            CTG
            @if($recentEventsNumbers['times_ctg'] > 0)
            <a href="/ctg/list?email={{$client_email}}" target="_blank">
            @endif
            <input type='button' value="{{$recentEventsNumbers['times_ctg']}}" size="2" readonly style="margin-right:3px" />
            @if($recentEventsNumbers['times_ctg'] > 0)
            </a>
            @endif

            RSG
            @if($recentEventsNumbers['times_rsg'] > 0)
            <a href="/rsgrequests?email={{$client_email}}" target="_blank">
            @endif
            <input type='button' value="{{$recentEventsNumbers['times_rsg']}}" size="2" readonly style="margin-right:3px" />
            @if($recentEventsNumbers['times_rsg'] > 0)
            </a>
            @endif

            NRW
            @if($recentEventsNumbers['times_negative_review'] > 0)
            <a href="/review?email={{$client_email}}" target="_blank">
            @endif
            <input type='button' value="{{$recentEventsNumbers['times_negative_review']}}" size="2" readonly style="margin-right:3px" />
            @if($recentEventsNumbers['times_negative_review'] > 0)
            </a>
            @endif
        </div>

    </div>

    <div class="right_bar_div">
        <label class="head_title">SG Tasks</label>
        <div>
            @include('inbox.task')
        </div>
    </div>

    <div class="panel panel-default">
        <a class="panel-heading">
            <h4 class="panel-title">Product Manuals</h4>
        </a>
        <div class="panel-collapse collapse in">
            <ul class="list-group" id="ulManual">
                <li class="list-group-item blink" style="padding:2em;">Data is Loading ...</li>
            </ul>
            <script type="text/template">
                <% if(manuals.length){ %>
                    <% for (let {link} of manuals){ %>
                        <li class="list-group-item"><a href="${link}" target="_blank">${getUrlFileName(link)}</a></li>
                    <% } %>
                <% }else{ %>
                    <li class="list-group-item" style="padding:2em;">Nothing to Show.</li>
                <% } %>
            </script>
        </div>
    </div>

    <div class="panel panel-default">
        <a class="panel-heading">
            <h4 class="panel-title">Product Videos</h4>
        </a>
        <div class="panel-collapse collapse in">
            <ul class="list-group" id="ulVideo">
                <li class="list-group-item blink" style="padding:2em;">Data is Loading ...</li>
            </ul>
            <script type="text/template">
                <% if(videos.length){ %>
                    <% for (let {link} of videos){ %>
                        <li class="list-group-item"><a href="${link}" target="_blank">${getUrlFileName(link)}</a></li>
                    <% } %>
                <% }else{ %>
                    <li class="list-group-item" style="padding:2em;">Nothing to Show.</li>
                <% } %>
            </script>
        </div>
    </div>

    <div class="panel panel-default">
        <a class="panel-heading">
            <h4 class="panel-title">Product Guide</h4>
        </a>
        <div class="panel-collapse collapse in">
            <ul class="list-group">
                <li class="list-group-item" style="padding:2em;">
                    <a href="/kms/productguide" target="_blank">Search More <i class="blink">...</i></a>
                </li>
            </ul>
        </div>
    </div>
</div>
@php
    $asinRows = (function()use($order){

        if(empty($order->item)) return [];

        $items = $order->item->toArray();

        $site = strtolower("www.{$order->SalesChannel}");

        return array_map(function($item)use($site){return ['site'=>$site, 'sellersku'=>$item->SellerSKU, 'asin'=>$item->ASIN];}, $items);
    })();
@endphp
<script>
    $(function ($) {
        // Accordion
        $('#email-detail-right-bar').on('click', '.panel-heading', e => $(e.currentTarget).next().collapse('toggle'))

        let asinRows = @json($asinRows)

        $.ajax({
            method: 'POST',
            data: {asinRows},
            url: '/kms/email-detail-right-bar-data',
            headers: {'X-CSRF-TOKEN': '{!! csrf_token() !!}'},
            success({manuals, videos}) {

                $(ulManual).html(tplRender(ulManual.nextElementSibling, {manuals}))

                $(ulVideo).html(tplRender(ulVideo.nextElementSibling, {videos}))

            }
        })
    })
</script>
