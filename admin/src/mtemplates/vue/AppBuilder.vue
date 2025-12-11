<script setup>
import Builder from "./Builder.vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";
import {
  ref
} from "vue";

const categories = ref([]);
const tags = ref([]);

const { isLoading, isError, isFetching, data, error, refetch } = useQuery({
  queryKey: ["mtemplates-taxonomies"],
  queryFn: getMTemplatesTaxonomies,
});

async function getMTemplatesTaxonomies() {
  let ret = {};
  const response = await doAjax({
    action: "direktt_get_mtemplates_taxonomies",
  });

  ret = response.data;
  categories.value = response.data.categories
  tags.value = response.data.tags
  return ret;
}

async function doAjax(args) {
  let result;
  try {
    result = await jQuery.ajax({
      url: direktt_mtemplates_object.ajaxurl,
      type: 'POST',
      data: args
    });
    return result;
  } catch (error) {
    throw (error)
  }
}

</script>

<template>

  <p></p>

  <Builder :categories="categories" :tags="tags"></Builder>

  <p></p>

</template>

<style></style>
