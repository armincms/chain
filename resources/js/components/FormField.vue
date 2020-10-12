<template>
  <div>
    <component 
      v-for="(field, index) in fields"
      :key="index"
      :is="`form-${field.component}`"
      :errors="errors"
      :resource-id="resourceId"
      :resource-name="resourceName"
      :field="field"
      :via-resource="viaResource"
      :via-resource-id="viaResourceId"
      :via-relationship="viaRelationship"
      :shown-via-new-relation-modal="shownViaNewRelationModal"
      @file-deleted="$emit('update-last-retrieved-at-timestamp')"  
      @hook:updated="updated(field)" 
    /> 
  </div>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
  mixins: [FormField, HandlesValidationErrors], 

  props: {  
    resourceId: {
      type: [Number, String],
    }, 
  }, 

  data: () => ({ 
    fields: [], 
    form: {
      type: Object,
      default: {}
    }  
  }),

  created() {     
    this.setInitialValue();

    if(this.field.events.length > 0) {
      Nova.$on('chain.updated', this.chainUpdated)  
    } else {
      this.getFields(this.form)
    }
  }, 

  computed: {
    availableFields: function() { 
      return this.fields.length ? this.fields : []
    }
  },

  methods: { 
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() { 
      this.form = new FormData;
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) { 
      if(this.fields.length) {
        this.fields.forEach(field => field.fill(formData))   
      } 
    },

    /**
     * Update the field's internal value.
     */
    handleChange(value) {
      this.value = value
    },

    updated: function (field) {     
      Nova.$emit('chain.updated', this.field, field) 
    },  

    chainUpdated: function(chain, field) {
      var events = [chain.attribute, chain.attribute +'.'+ field.attribute];

      if(_.intersection(this.field.events, events).length == 0) {
        return;
      }

      chain.fill(this.form)

      this.getFields(this.form);
    }, 

    /**
     * Get the available fields for the resource.
     */
    async getFields(formData) { 
      return await Nova.request()
        .post(
          `/nova-api/${this.resourceName}/chain-fields`,
          formData,
          {
            params: { 
              field: this.field.attribute, 
              resourceId: this.resourceId,
              viaResource: this.viaResource,
              viaResourceId: this.viaResourceId,
              viaRelationship: this.viaRelationship,
            },
          }
        )
        .catch(error => {
          if (error.response.status == 404) {
            // this.$router.push({ name: '404' })
            return
          }
        })
        .then(({ data }) => { 
          this.fields = data
          
          _.each(this.fields, field => {
            field.fill = () => ''   
          })
        })  
    },
  }
}
</script>
