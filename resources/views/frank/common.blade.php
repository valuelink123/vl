<script>
    try {
        eval('()=>{}')
    } catch (e) {
        var error = 'This application requires the latest Google Chrome.'
        confirm(error) && (location = 'https://www.google.com/chrome/')
    }

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': '{!! csrf_token() !!}'}
    })
</script>
<script src="/js/frank/prototypes.js?v={!! $jsversion = time() !!}"></script>
<script src="/js/frank/functions.js?v={!! $jsversion !!}"></script>
<script src="/js/frank/linkageInput.js?v={!! $jsversion !!}"></script>
<script src="/js/frank/xformhelper.js?v={!! $jsversion !!}"></script>
<script src="/js/frank/jstemplate.js?v={!! $jsversion !!}"></script>
<script>

</script>