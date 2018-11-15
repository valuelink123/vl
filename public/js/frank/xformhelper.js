class XFormHelper {

    static autoTrim(selector, childSelector = null) {

        function handle(e) {
            let $this = $(e.target)
            $this.val($this.val().trim())
        }

        if (childSelector) {
            $(selector).on('change', childSelector, handle)
        } else {
            $(selector).on('change', handle)
        }
    }

    static initByQuery(selector) {

        let obj = queryStringToObject()

        $(selector).each((i, ele) => {

            let $element = $(ele)
            let initByQuery = $element.data('init-by-query')

            if (!initByQuery) return

            try {
                $element.val(eval(`obj.${initByQuery}`))
            } catch (e) {

            }
        })

    }

    static inputDisableByRadio(selector) {
        $(selector).each((i, input) => {
            let $input = $(input)
            let $form = $input.closest('form')
            if (!$form.length) return
            let radioName = $input.data('assoc-radio')
            $input[0].disabled = parseInt($form[0][radioName].value) < 1
            $form.change(e => {
                if (radioName === e.target.name) {
                    $input[0].disabled = parseInt(e.target.value) < 1
                }
            })
        })
    }
}

$(() => {
    XFormHelper.autoTrim('.xform-autotrim')
    // XFormHelper.initByQuery('[data-init-by-query]')
})
