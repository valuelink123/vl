<style>
    .container-top-msg {
        margin-top: 25px;
    }

    .container-top-msg .alert {
        margin-bottom: 0;
    }

    .form-group label:only-child {
        width: 100%;
        margin-bottom: 0;
    }

    .form-group label .form-control {
        margin-top: 5px;
    }
</style>
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

    $(document).ajaxError(function (evt, xhr) {

        if (0 === xhr.status - 401) {
            confirm('The session has expired. Login again?') && location.reload()
        }

        console.log(evt, xhr)
    })
</script>
<script src="/js/frank/prototypes.js?v={!! $jsversion = 333 !!}"></script>
<script src="/js/frank/functions.js?v={!! $jsversion !!}"></script>
<script src="/js/frank/linkageinput.js?v={!! $jsversion !!}"></script>
<script src="/js/frank/xformhelper.js?v={!! $jsversion !!}"></script>
<script src="/js/frank/jstemplate.js?v={!! $jsversion !!}"></script>
<script>

</script>