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
}

$(() => {
    XFormHelper.autoTrim('.xform-autotrim')
    // XFormHelper.initByQuery('[data-init-by-query]')
})
