/**
 * 多个 INPUT 联动
 */
class LinkageInput {

    constructor(eleInputs, data) {
        LinkageInput.initLinkage(eleInputs, data)
    }


    /**
     * setDataList
     * @param eleInput
     * @param values
     */
    static setDataList(eleInput, values) {

        let dataListId = 'list-' + eleInput.id

        let eleDataList = document.getElementById(dataListId)

        if (!eleDataList) {
            eleDataList = document.createElement('datalist')
            eleInput.after(eleDataList) // 在DOM元素后面插入新元素
            eleDataList.id = dataListId
            eleInput.setAttribute('list', dataListId)
        }

        while (eleDataList.lastChild) {
            eleDataList.removeChild(eleDataList.lastChild)
        }

        for (let value of values) {
            let option = document.createElement('option')
            option.value = value
            eleDataList.appendChild(option)
        }
    }

    static initLinkage(eleInputs, treeData) {


        function getListByLayer(targetLayer) {

            let layerValueSet = new Set() // 使用集合来去重

            function walk(ref, layer = 0) {

                if (0 === layer - targetLayer) {

                    let values = (ref instanceof Array) ? ref : Object.keys(ref)

                    for (let value of values) layerValueSet.add(value)

                } else if (layer < targetLayer && !(ref instanceof Array)) {

                    let inputValue = eleInputs[layer].value

                    let refs = inputValue.trim() ? [ref[inputValue]] : Object.values(ref)

                    for (let ref of refs) ref && walk(ref, layer + 1)

                }

            }

            walk(treeData)

            return layerValueSet
        }

        for (let i = 0; i < eleInputs.length - 1; i++) {

            eleInputs[i].addEventListener('change', () => {
                let nextInput = eleInputs[i + 1]
                let values = getListByLayer(i + 1)
                if (!values.has(nextInput.value)) nextInput.value = ''
                // 通过代码修改 input.value，不会触发 change 事件，需要手动触发
                nextInput.dispatchEvent(new Event('change'))
            })
        }

        for (let i = 0; i < eleInputs.length; i++) {
            let eleInput = eleInputs[i]
            eleInput.addEventListener('focus', () => {
                let lastfilter = eleInputs.slice(0, i).map(ele => ele.value.trim()).join('')
                if (eleInput.lastfilter !== lastfilter) {
                    eleInput.lastfilter = lastfilter
                    this.setDataList(eleInput, getListByLayer(i))
                }
            })
        }

    }

}
