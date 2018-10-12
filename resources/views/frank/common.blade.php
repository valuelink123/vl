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
<script src="/js/frank/functions.js?v=111"></script>
<script src="/js/frank/linkageInput.js?v=113"></script>
<script src="/js/frank/xformhelper.js?v=111"></script>
<script>

</script>