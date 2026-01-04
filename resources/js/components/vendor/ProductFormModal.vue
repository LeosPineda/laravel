<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="fixed inset-0 bg-black/50 transition-opacity"
        @click="close"
      ></div>

      <div class="flex min-h-full items-center justify-center p-4">
        <div
          class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all"
          @click.stop
        >
          <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-900">
                {{ isEditMode ? 'Edit Product' : 'Add New Product' }}
              </h3>
              <button
                @click="close"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <div v-if="loadingProduct" class="p-8 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
            <p class="text-gray-500 mt-4">Loading product...</p>
          </div>

          <form v-else @submit.prevent="submitForm" class="p-6 max-h-[80vh] overflow-y-auto">
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
              <div class="flex items-start gap-4">
                <div class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300">
                  <img
                    v-if="imagePreview"
                    :src="imagePreview"
                    alt="Preview"
                    class="w-full h-full object-cover"
                  />
                  <span v-else class="text-3xl">🍔</span>
                </div>

                <div class="flex-1">
                  <input
                    ref="imageInput"
                    type="file"
                    accept="image/*"
                    @change="handleImageSelect"
                    class="hidden"
                  />
                  <button
                    type="button"
                    @click="$refs.imageInput.click()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                  >
                    {{ form.image ? 'Change Image' : 'Upload Image' }}
                  </button>
                  <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF up to 2MB</p>
                  <p v-if="form.image" class="text-xs text-green-600 mt-1">
                    ✓ {{ form.image.name }}
                  </p>
                </div>
              </div>
              <p v-if="errors.image" class="text-red-600 text-sm mt-1">{{ errors.image }}</p>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Product Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                placeholder="e.g., Cheese Burger"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                :class="{ 'border-red-500': errors.name }"
              />
              <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Price (₱) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.price"
                  type="number"
                  step="0.01"
                  min="0.01"
                  placeholder="0.00"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                  :class="{ 'border-red-500': errors.price }"
                />
                <p v-if="errors.price" class="text-red-600 text-sm mt-1">{{ errors.price }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Stock Quantity <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.stock_quantity"
                  type="number"
                  min="0"
                  placeholder="0"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                  :class="{ 'border-red-500': errors.stock_quantity }"
                />
                <p v-if="errors.stock_quantity" class="text-red-600 text-sm mt-1">{{ errors.stock_quantity }}</p>
              </div>
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <div class="relative">
                <input
                  v-model="form.category"
                  type="text"
                  list="categories"
                  placeholder="e.g., Burgers, Drinks, Desserts"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                />
                <datalist id="categories">
                  <option v-for="cat in categories" :key="cat" :value="cat" />
                </datalist>
              </div>
              <p class="text-xs text-gray-500 mt-1">Select existing or type new category</p>
            </div>

            <div class="mb-6 border-t border-gray-200 pt-6">
              <div class="flex items-center justify-between mb-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Add-ons</label>
                  <p class="text-xs text-gray-500">Optional extras customers can add</p>
                </div>
                <button
                  type="button"
                  @click="addAddon"
                  class="px-3 py-1.5 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 text-sm font-medium"
                >
                  + Add
                </button>
              </div>

              <div v-if="form.addons.length === 0" class="text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <span class="text-2xl">🧀</span>
                <p class="text-sm text-gray-500 mt-2">No add-ons yet</p>
                <p class="text-xs text-gray-400">Add extras like cheese, bacon, etc.</p>
              </div>

              <div v-else class="space-y-2">
                <div
                  v-for="(addon, index) in form.addons"
                  :key="addon.id || index"
                  class="flex gap-2 items-center bg-gray-50 p-2 rounded-lg"
                >
                  <div class="flex-1">
                    <input
                      v-model="addon.name"
                      type="text"
                      placeholder="Add-on name (e.g., Extra Cheese)"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                      :class="{ 'border-red-500': errors[`addons.${index}.name`] }"
                    />
                  </div>
                  <div class="w-28">
                    <div class="relative">
                      <span class="absolute left-3 top-2 text-gray-500 text-sm">₱</span>
                      <input
                        v-model.number="addon.price"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0"
                        class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                        :class="{ 'border-red-500': errors[`addons.${index}.price`] }"
                      />
                    </div>
                  </div>
                  <button
                    type="button"
                    @click="removeAddon(index)"
                    class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded"
                    title="Remove add-on"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <div v-if="submitError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-red-600 text-sm">{{ submitError }}</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
              <button
                type="button"
                @click="close"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50"
              >
                {{ submitting ? 'Saving...' : (isEditMode ? 'Update Product' : 'Create Product') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { apiGet, apiPost, apiPut, apiUpload, apiDelete } from '@/composables/useApi'

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  productId: { type: [Number, String], default: null },
  categories: { type: Array, default: () => [] }
})

const emit = defineEmits(['close', 'saved'])

const isEditMode = computed(() => !!props.productId)

const form = ref({
  name: '',
  price: null,
  stock_quantity: 0,
  category: '',
  image: null,
  addons: []
})

const errors = ref({})
const submitError = ref('')
const submitting = ref(false)
const loadingProduct = ref(false)
const imagePreview = ref(null)
const existingImageUrl = ref(null)
const originalAddons = ref([])

const resetForm = () => {
  form.value = {
    name: '',
    price: null,
    stock_quantity: 0,
    category: '',
    image: null,
    addons: []
  }
  errors.value = {}
  submitError.value = ''
  imagePreview.value = null
  existingImageUrl.value = null
  originalAddons.value = []
}

const addAddon = () => {
  form.value.addons.push({ id: null, name: '', price: null, isNew: true })
}

const removeAddon = (index) => {
  form.value.addons.splice(index, 1)
}

const handleImageSelect = (event) => {
  const file = event.target.files[0]
  if (!file) return

  if (file.size > 2 * 1024 * 1024) {
    errors.value.image = 'Image must be less than 2MB'
    return
  }

  const allowedTypes = ['image/jpeg', 'image/png', 'image/gif']
  if (!allowedTypes.includes(file.type)) {
    errors.value.image = 'Only JPG, PNG, and GIF files are allowed'
    return
  }

  errors.value.image = null
  form.value.image = file

  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const validateForm = () => {
  errors.value = {}

  if (!form.value.name?.trim()) {
    errors.value.name = 'Product name is required'
  }

  if (!form.value.price || form.value.price <= 0) {
    errors.value.price = 'Price must be greater than 0'
  }

  if (form.value.stock_quantity === null || form.value.stock_quantity < 0) {
    errors.value.stock_quantity = 'Stock quantity must be 0 or greater'
  }

  form.value.addons.forEach((addon, index) => {
    if (addon.name?.trim() && (addon.price === null || addon.price < 0)) {
      errors.value[`addons.${index}.price`] = 'Price required'
    }
  })

  return Object.keys(errors.value).length === 0
}

const loadProduct = async () => {
  if (!props.productId) return

  loadingProduct.value = true
  try {
    const response = await apiGet(`/api/vendor/products/${props.productId}`)

    if (response.ok) {
      const data = await response.json()
      const product = data.product

      const addons = (product.addons || []).map(a => ({
        id: a.id,
        name: a.name,
        price: parseFloat(a.price),
        isNew: false
      }))

      form.value = {
        name: product.name,
        price: parseFloat(product.price),
        stock_quantity: product.stock_quantity,
        category: product.category || '',
        image: null,
        addons: addons
      }

      originalAddons.value = JSON.parse(JSON.stringify(addons))

      if (product.image_url) {
        existingImageUrl.value = product.image_url
        imagePreview.value = product.image_url.startsWith('http')
          ? product.image_url
          : `/storage/${product.image_url}`
      }
    } else {
      const error = await response.json()
      submitError.value = error.error || 'Failed to load product'
    }
  } catch (e) {
    console.error('Error loading product:', e)
    submitError.value = 'Failed to load product'
  } finally {
    loadingProduct.value = false
  }
}

const submitForm = async () => {
  if (!validateForm()) return

  submitting.value = true
  submitError.value = ''

  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('price', form.value.price.toString())
    formData.append('stock_quantity', form.value.stock_quantity.toString())
    if (form.value.category) {
      formData.append('category', form.value.category)
    }
    if (form.value.image) {
      formData.append('image', form.value.image)
    }

    if (!isEditMode.value) {
      form.value.addons.forEach((addon, index) => {
        if (addon.name?.trim()) {
          formData.append(`addons[${index}][name]`, addon.name)
          formData.append(`addons[${index}][price]`, (addon.price || 0).toString())
          formData.append(`addons[${index}][is_active]`, 'true')
        }
      })
    }

    let url = '/api/vendor/products'
    if (isEditMode.value) {
      url = `/api/vendor/products/${props.productId}`
      formData.append('_method', 'PUT')
    }

    const response = await apiUpload(url, formData)

    if (!response.ok) {
      const error = await response.json()
      if (error.errors) {
        Object.keys(error.errors).forEach(key => {
          errors.value[key] = error.errors[key][0]
        })
      } else {
        submitError.value = error.error || error.message || 'Failed to save product'
      }
      return
    }

    const data = await response.json()
    const productId = data.product?.id || props.productId

    if (isEditMode.value && productId) {
      await syncAddons(productId)
    }

    emit('saved', data.product)
    close()
  } catch (e) {
    console.error('Error saving product:', e)
    submitError.value = 'Failed to save product'
  } finally {
    submitting.value = false
  }
}

const syncAddons = async (productId) => {
  const currentAddons = form.value.addons.filter(a => a.name?.trim())
  const originalIds = originalAddons.value.map(a => a.id)
  const currentIds = currentAddons.filter(a => a.id).map(a => a.id)

  const deletedIds = originalIds.filter(id => !currentIds.includes(id))

  for (const addonId of deletedIds) {
    try {
      await apiDelete(`/api/vendor/addons/${addonId}`)
    } catch (e) {
      console.error('Error deleting addon:', e)
    }
  }

  for (const addon of currentAddons) {
    try {
      if (addon.isNew || !addon.id) {
        await apiPost(`/api/vendor/products/${productId}/addons`, {
          name: addon.name,
          price: addon.price || 0,
          is_active: true
        })
      } else {
        const original = originalAddons.value.find(a => a.id === addon.id)
        if (original && (original.name !== addon.name || original.price !== addon.price)) {
          await apiPut(`/api/vendor/addons/${addon.id}`, {
            name: addon.name,
            price: addon.price || 0
          })
        }
      }
    } catch (e) {
      console.error('Error saving addon:', e)
    }
  }
}

const close = () => {
  resetForm()
  emit('close')
}

watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      if (props.productId) {
        loadProduct()
      } else {
        resetForm()
      }
    }
  }
)

watch(
  () => props.productId,
  (newId) => {
    if (newId && props.isOpen) {
      loadProduct()
    }
  }
)
</script>
