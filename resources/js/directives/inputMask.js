import Inputmask from "inputmask";

export default {
    mounted(el, binding) {
        Inputmask(binding.value).mask(el);
    },
    updated(el, binding) {
        Inputmask(binding.value).mask(el);
    }
};
