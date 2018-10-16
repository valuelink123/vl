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
</style>
<script src="/js/frank/functions.js?v={!! time() !!}"></script>
<div class="panel-group" id="email-detail-right-bar">
    <div class="panel panel-default">
        <a class="panel-heading">
            <h4 class="panel-title">Product Manuals</h4>
        </a>
        <div class="panel-collapse collapse in">
            <ul class="list-group" id="ulManual">
                <li class="list-group-item blink" style="padding:2em;">Data is Loading ...</li>
            </ul>
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
        </div>
    </div>

    <div class="panel panel-default">
        <a class="panel-heading">
            <h4 class="panel-title">Product Guide</h4>
        </a>
        <div class="panel-collapse collapse in">
            <ul class="list-group">
                <li class="list-group-item" style="padding:2em;">
                    <a href="/kms/brandline" target="_blank">Search More <i class="blink">...</i></a>
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

                if (manuals.length) {
                    let lis = []
                    for (let {link} of manuals) {
                        lis.push(`<li class="list-group-item"><a href="${link}" target="_blank">${getUrlFileName(link)}</a></li>`)
                    }
                    $(ulManual).html(lis.join(''))
                } else {
                    $(ulManual).children().removeClass('blink').html('Nothing to Show.')
                }


                if (videos.length) {
                    let lis = []
                    for (let {link} of videos) {
                        lis.push(`<li class="list-group-item"><a href="${link}" target="_blank">${getUrlFileName(link)}</a></li>`)
                    }
                    $(ulVideo).html(lis.join(''))
                } else {
                    $(ulVideo).children().removeClass('blink').html('Nothing to Show.')
                }

            }
        })
    })
</script>
