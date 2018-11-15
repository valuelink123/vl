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

    static inputEnableByRadio(formSelector) {
        $(formSelector).each((i, form) => {

            let $form = $(form);

            let map = new Map($form.find('[data-enable-radio]').toArray().map(input => [$(input).data('enable-radio'), input]))

            if (!map.size) return

            $form.change(e => {
                let $radio = $(e.target)
                if (!$radio.is(':radio')) return
                if (map.has(e.target.name)) {
                    let input = map.get(e.target.name)
                    input.disabled = parseInt(e.target.value) < 1
                }
            })

            $form.find(':radio:checked').change()
        })
    }
}

$(() => {
    XFormHelper.autoTrim('.xform-autotrim')
    // XFormHelper.initByQuery('[data-init-by-query]')
})
