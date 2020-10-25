<template>
    <input :class="[baseClasses, additionalClasses]" :value="value" @input="$emit('input', $event.target.value)" ref="input">
</template>

<script>
    export default {
        props: [
            'value',
            {
                additionalClasses: {
                    type: String,
                    default: ""
                }
            }
        ],
        data () {
            return {
                baseClasses: "form-input rounded-md shadow-sm"
            }
        },

        methods: {
            focus() {
                this.$refs.input.focus()
            }
        }
    }
</script>

