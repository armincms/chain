<template>
  <loading-view :loading="loading">
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
  </loading-view>
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
    loading: false,
    form: {
      type: Object,
      default: {}
    }  
  }),

  created() {     
    this.setInitialValue();

    if(this.field.events.length > 0) {
      Nova.$on(this.events, this.chainUpdated)   
    } else {
      this.getFields(this.form)
    }
  }, 

  beforeDestroy() {
    Nova.$off(this.events, this.chainUpdated)
  },

  computed: {
    availableFields: function() { 
      return this.fields.length ? this.fields : []
    },

    events: function() { 
      return this.field.events.map((event) => this.attributeEvent(event))
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
      Nova.$emit(this.attributeEvent(field.attribute), this.field, field) 
      Nova.$emit(this.attributeEvent(this.field.attribute), this.field, field)  
    },  

    /**
     * Make event ley for the given attribute 
     */
    attributeEvent(event) {
      return 'chain.' + event;
    },

    /**
     * Handles events.
     */
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
      this.fields = [];
      this.loading= true;

      await Nova.request()
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
            this.loading= false
            return
          }
        })
        .then(({ data }) => { 
          this.fields = data
          this.loading= false
          
          _.each(this.fields, field => {
            field.fill = () => '' 
          })
        })  
    },
  }
}
</script>
