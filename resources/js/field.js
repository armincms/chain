Nova.booting((Vue, router, store) => {
  Vue.component('index-chain', require('./components/IndexField'))
  Vue.component('detail-chain', require('./components/DetailField'))
  Vue.component('form-chain', require('./components/FormField'))
})
