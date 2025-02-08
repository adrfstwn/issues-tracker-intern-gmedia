<script setup>
import DefaultLayout from "@/layout/DefaultLayout.vue";
import SearchForms from "@/components/searchforms/SearchForms.vue";

import { ref } from "vue";

const isOpen = ref(false);
const isVisible = ref(false);

const ontoggle = () => {
  isOpen.value = !isOpen.value;
  isVisible.value = !isVisible.value;
};
</script>

<template>
  <DefaultLayout>
    <div class="min-h-screen bg-white rounded-2xl">
      <div class="mx-4 bg-white rounded-2xl">
        <div class="min-h-screen flex flex-col gap-4 rounded-2xl bg-white">
          <div class="px-6 w-full flex flex-col justify-between gap-3">
            <div class="pt-6">
              <h4 class="text-base md:text-xl font-bold text-cobalt-950">
                Role Access Control
              </h4>
            </div>
            <SearchForms class="" />
          </div>

          <div
            class="overflow-x-auto scrollbar-hide rounded-2xl border border-wildsand-200"
          >
            <table class="min-w-max w-full table-auto md:table-fixed">
              <thead>
                <tr
                  class="bg-wildsand-100 text-codgray-950 capitalize text-sm leading-normal rounded-t-2xl"
                >
                  <th class="px-6 py-3 text-left font-semibold">ID</th>
                  <th class="px-6 py-3 text-left font-semibold">Name</th>
                  <th
                    class="px-6 py-3 text-left font-semibold hidden sm:table-cell"
                  >
                    Email
                  </th>
                  <th class="px-6 py-3 text-left font-semibold">Role</th>
                  <th class="px-6 py-3 text-left font-semibold">Status</th>
                  <th class="px-6 py-3 text-left font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody class="text-codgray-800 text-base">
                <tr
                  class="border-t bg-white border-wildsand-200 hover:bg-wildsand-50/70"
                >
                  <td class="px-6 py-2 uppercase font-medium">id</td>
                  <td class="px-4 py-2 uppercase">name</td>
                  <td class="px-4 py-2 uppercase hidden sm:table-cell">email</td>
                  <td class="px-4 py-2 uppercase">roles</td>
                  <td class="px-4 py-2 uppercase">status</td>
                  <td class=" py-4 px-1">
                    <div class="">
                        <button
                          type="button"
                          @click="ontoggle"
                          class="px-4 py-2 rounded-xl bg-gradient-to-b from-cobalt-700 to-cobalt-900 text-white text-sm hover:shadow-md hover:shadow-cobalt-700/25 hover:transition hover:ease-in-out"
                        >
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24px"
                            height="24px"
                            viewBox="0 0 24 24"
                          >
                            <g
                              fill="none"
                              stroke="currentColor"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.5"
                            >
                              <path
                                d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2S2 6.477 2 12s4.477 10 10 10M7 9l5 1m5-1l-5 1m0 0v3m0 0l-2 5m2-5l2 5"
                              />
                              <path
                                fill="currentColor"
                                d="M12 7a.5.5 0 1 1 0-1a.5.5 0 0 1 0 1"
                              />
                            </g>
                          </svg>
                        </button>
                        <div
                          v-if="isVisible"
                          class="fixed inset-0 z-50 flex items-center justify-center"
                        >
                          <div
                            class="absolute bg-codgray-950/40 inset-0 z-0 backdrop-blur-sm"
                            @click="isVisible = false"
                          ></div>
                          <!--  modal -->
                          <div
                            class="w-full max-w-5xl mx-auto rounded-xl shadow-md bg-white relative z-10 flex flex-col gap-6"
                          >
                            <!-- header modal -->
                            <div class="px-6 py-3 bg-whiteBgPrimary-100 rounded-xl">
                              <h4 class="text-base font-semibold text-codgray-950">
                                Role Access Control
                              </h4>
                            </div>
                            <!-- body modal -->
                            <div class="flex flex-col gap-4 justify-end w-full">
                              <div class="px-6 py-3">
                                <table
                                  class="min-w-max w-full table-auto md:table-fixed rounded-lg overflow-hidden shadow-sm"
                                >
                                  <thead>
                                    <tr
                                      class="border border-wildsand-200 text-codgray-950 capitalize text-sm leading-normal"
                                    >
                                      <th
                                        class="px-6 py-3 text-left font-semibold rounded-tl-lg"
                                      >
                                        Menu
                                      </th>
                                      <th
                                        class="px-6 py-3 text-left font-semibold rounded-tr-lg"
                                      >
                                        Action
                                      </th>
                                    </tr>
                                  </thead>
                                  <tbody class="text-codgray-800 text-base">
                                    <tr
                                      class="border-t bg-white border-wildsand-200 hover:bg-wildsand-50/70"
                                    >
                                      <td class="px-6 py-3 font-medium">
                                        <div class="flex items-center">
                                          <input
                                            type="checkbox"
                                            id="issue"
                                            name="issue"
                                            class="mr-2 size-5"
                                          />
                                          <label for="issue">Issues</label>
                                        </div>
                                      </td>
                                      <td class="px-6 py-3 flex items-center gap-2">
                                        <div class="flex items-center">
                                          <input
                                            type="radio"
                                            name="resolved"
                                            id="resolved"
                                            class="size-5 mr-2"
                                          />
                                          <label for="resolved">Resolved</label>
                                        </div>
                                        <div class="flex items-center">
                                          <input
                                            type="radio"
                                            name="ignored"
                                            id="ignored"
                                            class="size-5 mr-2"
                                          />
                                          <label for="ignored">Ignored</label>
                                        </div>
                                      </td>
                                    </tr>
                                    <tr
                                      class="border-t bg-white border-wildsand-200 hover:bg-wildsand-50/70"
                                    >
                                      <td class="px-6 py-3 font-medium">
                                        <div class="flex items-center">
                                          <input
                                            type="checkbox"
                                            id="user"
                                            name="user"
                                            class="mr-2 size-5"
                                          />
                                          <label for="user">List Users</label>
                                        </div>
                                      </td>
                                      <td class="px-6 py-3 flex items-center gap-2">
                                        <div class="flex items-center">
                                          <input
                                            type="radio"
                                            name="create"
                                            id="create"
                                            class="size-5 mr-2"
                                          />
                                          <label for="create">Create</label>
                                        </div>
                                        <div class="flex items-center">
                                          <input
                                            type="radio"
                                            name="update"
                                            id="update"
                                            class="size-5 mr-2"
                                          />
                                          <label for="update">Update</label>
                                        </div>
                                        <div class="flex items-center">
                                          <input
                                            type="radio"
                                            name="delete"
                                            id="delete"
                                            class="size-5 mr-2"
                                          />
                                          <label for="delete">Delete</label>
                                        </div>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                              </div>
                              <!-- footer button -->
                              <div
                                class="p-6 flex gap-2 items-center w-full justify-end"
                              >
                                <button
                                  @click="isVisible = false"
                                  class="px-4 py-2 rounded-lg bg-gradient-to-b text-sm text-cobalt-700 border border-cobalt-700"
                                >
                                  Close
                                </button>
                                <button
                                  @click="isVisible = false"
                                  class="px-4 py-2 rounded-lg bg-gradient-to-b from-cobalt-700 to-cobalt-900 text-sm text-white"
                                >
                                  Save
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>