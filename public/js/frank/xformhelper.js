class XFormHelper {

    static autoTrim(selector) {
        $(selector).change(e => {
            let $this = $(e.target)
            $this.val($this.val().trim())
        })
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
