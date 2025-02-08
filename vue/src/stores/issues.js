import { ref } from "vue";
import { defineStore } from "pinia";

import {
  allListIssuesApi,
  detailIssuesApi,
  detailEventsIssuesApi,
  detailTraceIssuesApi,
  editStatusIssueApi,
  createWorkPackagesApi,
  userAssigneeeApi,
  dataAssigneedApi,
  rootProjectsApi,
  updateIssueWorkPackagesApi,
} from "@/api/issues-api";

// Move isValidId to the store since it's used here
const isValidId = (id) => {
  return id !== undefined && !isNaN(id) && parseInt(id) > 0;
};

export const useIssuesStore = defineStore("useIssuesStore", () => {
  const allIssues = ref([]);
  const detailIssues = ref(null);
  const detailEventsIssue = ref([]);
  const traceIssue = ref(null);
  const editStatus = ref(null);
  const assignIssue = ref(null);
  const isAssigned = ref(false);
  const userAssignee = ref(null);
  const dataAssignee = ref(null);
  const dataRootProejcts = ref(null);
  const paginationAllIssues = ref({
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
  });

  const paginationEventsIssue = ref({
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
  });

  const isLoading = ref(false);
  const error = ref(null);

  // Reset state helper
  const resetState = () => {
    detailIssues.value = null;
    error.value = null;
  };

  // Notification
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

  // handle fetch api edit status api
  const fetchEditStatusApi = async (id, status) => {
    isLoading.value = true;
    resetState();

    try {
      const response = await editStatusIssueApi(id, { status });
      editStatus.value = response.data;
      showNotification(`Issue status successfully changed to ${status}! 😊`);
    } catch (err) {
      console.error("Filed to change issue status:", err);
      showNotification(
        "Failed to change issue status. Please try again.",
        "error"
      );
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  // handle feth api update issue to work packages
  const fetchUpdateWorkPackagesApi = async (id, { assignee_id }) => {
    isLoading.value = true;
    resetState();
    try {
      const response = await updateIssueWorkPackagesApi(id, {
        assignee_id: assignee_id || null,
      });
      showNotification(`Assignment successfully updated! 😊`);
      return response.data;
    } catch (err) {
      console.error("Failed to update work package:", err);
      showNotification(
        "Failed to update assignment. Please try again.",
        "error"
      );
      throw err;
    }
  };

  // handle fetch api create issue to work packages
  const fetchCreateWorkPackagesApi = async (id, { assignee_id }) => {
    isLoading.value = true;
    resetState();
    try {
      const response = await createWorkPackagesApi(id, {
        assignee_id: assignee_id || null,
      });
     // assignIssue.value = response.data;
      showNotification(
        `Issue successfully assigned to work packages open project! 😊`
      );
      return response.data;
    } catch (err) {
      console.error("issue has been assigned to work packages:", err);
      showNotification(
        "Failed to assign issue to work packages. Please try again.",
        "error"
      );
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  // handle fetch api user assignee, user with acccess to assign issue
  const fetchUserAssigneeApi = async () => {
    isLoading.value = true;
    try {
      const response = await userAssigneeeApi();
      userAssignee.value = response.data;
    } catch (err) {
      console.error("failed to get data user from assignee", err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  // handle fetch api data assignee
  const fetchDataAssigneeApi = async () => {
    isLoading.value = true;
    {
      try {
        const response = await dataAssigneedApi();
        dataAssignee.value = response.data.data;
      } catch (err) {
        console.error("failed to fetch data assigneed", err);
      }
    }
  };

  // handle fetch api data root projects
  const fetchRootProjectsApi = async () => {
    isLoading.value = true;
    try {
      const response = await rootProjectsApi();
      if (response.data.data && Array.isArray(response.data.data)) {
        dataRootProejcts.value = response.data.data.reduce((acc, project) => {
          acc[project.id] = project;
          return acc;
        }, {});
      }
    } catch (err) {
      console.error("failed to fetch data root projects", err);
      throw err;
    }
  };

  // handle Fetch api all issues
  const fetchAllIssueApi = async (page) => {
    isLoading.value = true;
    resetState();

    try {
      const response = await allListIssuesApi(page);

      if (response?.data?.pagination) {
        paginationAllIssues.value = {
          current_page: Number(response.data.pagination.current_page) || 1,
          per_page: Number(response.data.pagination.per_page) || 20,
          total: Number(response.data.pagination.total) || 0,
          last_page: Number(response.data.pagination.last_page) || 1,
          next_page_url: response.data.pagination.next_page_url || null,
          prev_page_url: response.data.pagination.prev_page_url || null,
        };
      }

      if (response?.data?.issues) {
        allIssues.value = response.data.issues;
      } else if (Array.isArray(response.data)) {
        allIssues.value = response.data;
      }

      return allIssues.value;
    } catch (err) {
      error.value = "Failed to fetch issues";
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  // handle Fetch api detail issues
  const fetchDetailIssueApi = async (id) => {
    if (!isValidId(id)) {
      error.value = "Invalid issue ID";
      throw new Error("Invalid issue ID");
    }

    isLoading.value = true;
    resetState();

    try {
      const response = await detailIssuesApi(id);
      if (response?.data?.issue) {
        detailIssues.value = response.data.issue;
      }
      return detailIssues.value;
    } catch (err) {
      error.value = "Failed to fetch issue details";
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  // handle fetch api events issue
  const fetchEventsIssueApi = async (id, page) => {
    isLoading.value = true;
    resetState();

    try {
      const response = await detailEventsIssuesApi(id, page);
      paginationEventsIssue.value = {
        current_page: page,
        per_page: 10, // Default value if not provided
        total: response.data.total || 0,
        last_page: Math.ceil((response.data.total || 0) / 15),
        next_page_url: response.data.next_page_url,
        prev_page_url: response.data.prev_page_url,
        first_page_url: response.data.first_page_url,
        last_page_url: response.data.last_page_url,
      };

      if (Array.isArray(response.data.data)) {
        detailEventsIssue.value = response.data.data;
      } else if (Array.isArray(response.data)) {
        detailEventsIssue.value = response.data;
      } else {
        detailEventsIssue.value = [];
      }
    } catch (err) {
      error.value = "failed to fetch events";
      detailEventsIssue.value = [];
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  // handle fetch api trace issue
  const fetchTraceIssue = async (id) => {
    isLoading.value = true;
    try {
      const response = await detailTraceIssuesApi(id);
      if (response?.data?.data) {
        traceIssue.value = response.data.data;
      } else if (Array.isArray(response.data)) {
        traceIssue.value = response.data;
      }
      return traceIssue.value;
    } catch (err) {
      error.value = "failed to fetch trace issue";
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    allIssues,
    detailIssues,
    detailEventsIssue,
    traceIssue,
    isLoading,
    error,
    paginationAllIssues,
    paginationEventsIssue,
    editStatus,
    assignIssue,
    notification,
    userAssignee,
    dataAssignee,
    dataRootProejcts,
    isAssigned,
    fetchUpdateWorkPackagesApi,
    fetchRootProjectsApi,
    fetchDataAssigneeApi,
    fetchUserAssigneeApi,
    showNotification,
    fetchCreateWorkPackagesApi,
    fetchEditStatusApi,
    fetchAllIssueApi,
    fetchDetailIssueApi,
    fetchEventsIssueApi,
    fetchTraceIssue,
    resetState,
  };
});
