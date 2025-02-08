import { ref } from "vue";
import { defineStore } from "pinia";

import {
  projectsListApi,
  projectsListEventApi,
  projectsListIssueApi,
  saveRootProjectsApi,
} from "@/api/projects-api";

export const useProjectsStore = defineStore("useProjectsStore", () => {
  const projectsList = ref([]);
  const projectsListIssue = ref([]);
  const projectsListEvents = ref([]);
  const rootProjects = ref(null);
  const isLoading = ref([]);
  const error = ref(null);
  const paginationMeta = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
  });
  const issuePaginationMeta = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
  });
  const eventsPaginationMeta = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
  });

  // reset state
  const resetState = () => {
    projectsList.value = [];
    projectsListIssue.value = [];
    projectsListEvents.value = [];
    error.value = null;
  };

  // notification status
  const notification = ref({
    show: false,
    message: "",
    type: "success",
  });

  // show notification function
  const showNotification = (message, type = "success") => {
    notification.value = {
      show: true,
      message,
      type,
    };

    setTimeout(() => {
      notification.value.show = false;
    }, 3000);
  };

  // handle fetch api all projects
  const fetchAllProjectsListApi = async (page = 1) => {
    isLoading.value = true;
    resetState();

    try {
      const response = await projectsListApi(page);

      // Handle pagination meta
      if (response.data.pagination) {
        paginationMeta.value = {
          current_page: page,
          per_page: 15,
          total: response.data.total || 0,
          last_page: response.data.pagination.last_page || 1,
          next_page_url: response.data.pagination.next_page_url,
          prev_page_url: response.data.pagination.prev_page_url,
          first_page_url: response.data.pagination.first_page_url,
          last_page_url: response.data.pagination.last_page_url,
        };
      }

      if (Array.isArray(response.data.data)) {
        projectsList.value = response.data.data;
      } else if (Array.isArray(response.data)) {
        projectsList.value = response.data;
      } else {
        projectsList.value = [];
        console.warn("No projects data found in response:", response);
      }
    } catch (err) {
      error.value = err.message || "Failed to fetch projects";
      projectsList.value = [];
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  // handle fetch api all issue projects
  const fetchAllProjectsIssueApi = async (slug, page = 1) => {
    isLoading.value = true;
    resetState();

    try {
      const response = await projectsListIssueApi(slug, page);

      issuePaginationMeta.value = {
        current_page: page,
        per_page: 15, 
        total: response.data.total || 0,
        last_page: Math.ceil((response.data.total || 0) / 15),
        next_page_url: response.data.next_page_url,
        prev_page_url: response.data.prev_page_url,
        first_page_url: response.data.first_page_url,
        last_page_url: response.data.last_page_url,
      };

      if (Array.isArray(response.data.data)) {
        projectsListIssue.value = response.data.data;
      } else if (Array.isArray(response.data)) {
        projectsListIssue.value = response.data;
      } else {
        projectsListIssue.value = [];
        console.warn("No issues data found in response:", response);
      }
    } catch (err) {
      error.value = "Failed to fetch issues";
      projectsListIssue.value = [];
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  // handle fetch api all event projects
  const fetchAllProjectsEventsApi = async (slug, page = 1) => {
    try {
      const response = await projectsListEventApi(slug, page);
      //projectsListEvents.value = data?.data || [];

      if (response?.data.pagination) {
        eventsPaginationMeta.value = {
          current_page: Number(response.data.pagination.current_page) || 1,
          per_page: Number(response.data.pagination.per_page) || 15,
          total: Number(response.data.pagination.total) || 0,
          last_page: Number(response.data.pagination.last_page) || 1,
          first_page_url: Number(response.data.pagination.first_page_url),
          last_page_url: Number(response.data.pagination.last_page_url),
          next_page_url: Number(response.data.pagination.next_page_url),
          prev_page_url: Number(response.data.pagination.prev_page_url),
        };
      }
      if (Array.isArray(response.data)) {
        projectsList.value = response.data;
        return response.data;
      } else if (Array.isArray(response.data.data)) {
        projectsList.value = response.data.data;
        return response.data.data;
      } else {
        console.error("Unexpected response structure:", response);
        throw new Error("Invalid response structure from API");
      }
    } catch (err) {
      error.value = errorMessage;
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  // handle fetch api save root projects 
  const fetchRootProjectApi = async (
    slug,
    { openproject_id, openproject_name }
  ) => {
    try {
      const response = await saveRootProjectsApi(slug, {
        openproject_id: openproject_id || null,
        openproject_name: openproject_name || null,
      });
      rootProjects.value = response.data;
      showNotification(
        `Root project successfully saved in! ${openproject_name} 😁`,
        "success"
      );
    } catch (err) {
      console.error("failed to send for root projects", err);
      showNotification(
        "Failed to save root project. Please try again.",
        "error"
      );
    }
  };

  return {
    projectsList,
    projectsListIssue,
    projectsListEvents,
    paginationMeta,
    eventsPaginationMeta,
    issuePaginationMeta,
    isLoading,
    error,
    rootProjects,
    notification,
    fetchRootProjectApi,
    fetchAllProjectsListApi,
    fetchAllProjectsIssueApi,
    fetchAllProjectsEventsApi,
    showNotification,
  };
});
