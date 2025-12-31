# ProductFormModal.vue - Truncated JavaScript Fix

## Complete the JavaScript section starting from line 443

Copy and paste this code to replace the truncated part in your ProductFormModal.vue file:

```javascript
          formData.append(`addons[${index}][price]`, (addon.price || 0).toString())
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

    // For edit mode, handle addon changes separately
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

// Sync addons - create new ones, update existing, delete removed
const syncAddons = async (productId) => {
  const currentAddons = form.value.addons.filter(a => a.name?.trim())
  const originalIds = originalAddons.value.map(a => a.id)
  const currentIds = currentAddons.filter(a => a.id).map(a => a.id)

  // Find deleted addons
  const deletedIds = originalIds.filter(id => !currentIds.includes(id))

  // Delete removed addons
  for (const addonId of deletedIds) {
    try {
      await apiDelete(`/api/vendor/addons/${addonId}`)
    } catch (e) {
      console.error('Error deleting addon:', e)
    }
  }

  // Create or update addons
  for (const addon of currentAddons) {
    try {
      if (addon.isNew || !addon.id) {
        // Create new addon
        await apiPost(`/api/vendor/products/${productId}/addons`, {
          name: addon.name,
          price: addon.price || 0
        })
      } else {
        // Check if addon was modified
        const original = originalAddons.value.find(a => a.id === addon.id)
        if (original && (original.name !== addon.name || original.price !== addon.price)) {
          // Update existing addon
          await apiPost(`/api/vendor/addons/${addon.id}`, {
            _method: 'PUT',
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

// Watch for modal open
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

// Watch for productId changes
watch(
  () => props.productId,
  (newId) => {
    if (newId && props.isOpen) {
      loadProduct()
    }
  }
)
</script>
```

## Instructions:

1. Open your `resources/js/components/vendor/ProductFormModal.vue` file
2. Find line 442 where it ends with: `formData.append(\`addons[${index}][price]\`,`
3. Replace everything from that line to the end with the complete JavaScript code above
4. Save the file

This will complete the truncated ProductFormModal.vue and fix the syntax errors.
