<template>
    <div class="relative flex w-full h-full bg-white p-2 md:p-52 justify-center">
        <collections-search context="search" :url="null" placeholder="Enter search terms" :filter_group_id="null"/>
    </div>
</template>


<script>
import CollectionsSearch from '../Components/search/CollectionsSearch/CollectionsSearch.vue'
    export default {
        components: {CollectionsSearch}
    }
</script>
