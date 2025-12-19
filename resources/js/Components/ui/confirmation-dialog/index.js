import { ref } from 'vue'

const isOpen = ref(false)
const title = ref('')
const description = ref('')
let resolver = () => {}

export function useConfirmDialog() {
  const init = (t, d = '') => {
    title.value = t
    description.value = d
    isOpen.value = true

    return new Promise((resolve) => {
      resolver = resolve
    })
  }

  const onConfirm = () => {
    isOpen.value = false
    resolver(true)
  }

  const onCancel = () => {
    isOpen.value = false
    resolver(false)
  }

  return {
    isOpen,
    title,
    description,
    init,
    onConfirm,
    onCancel,
  }
}

const instance = useConfirmDialog()

export const confirmModal = instance.init
export const confirmDialogState = instance
