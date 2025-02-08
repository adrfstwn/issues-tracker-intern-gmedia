import { defineStore } from "pinia";
import { ref, computed } from "vue";

import { AllStatusApi } from "@/api/dashboard-api";

export const useAllStatusStore = defineStore("useAllStatusStore", () => {
  const allStatus = ref({
    total: null,
    unresolved: null,
    resolved: null,
    ignored: null,
  });

  const isLoading = ref(false);
  const error = ref(null);

  // return total issues per status with default 0
  const totalIssues = computed(() => allStatus.value.total?.count || 0);
  const unresolvedIssues = computed(
    () => allStatus.value.unresolved?.count || 0
  );
  const resolvedIssues = computed(() => allStatus.value.resolved?.count || 0);
  const ignoredIssues = computed(() => allStatus.value.ignored?.count || 0);

  // reset state
  const resetState = () => {
    allStatus.value = {
      total: null,
      unresolved: null,
      resolved: null,
      ignored: null,
    };
    error.value = null;
  };

  // handle fetch api status issue by type (etc. solved or ignored)
  const fetchStatusByType = async (type, slug = " ") => {
    try {
      const response = await AllStatusApi(type, slug);
      allStatus.value[type] = response.data;
      return response.data;
    } catch (err) {
      console.error(`Error fetching ${type} status:`, err);
      error.value = `Failed to fetch ${type} status: ${err.message}`;
      throw error.value;
    }
  };

  // fetch all status api
  const fetchAllStatusApi = async (slug = " ", status = "total") => {
    resetState();
    try {
      const types =
        status === "total"
          ? ["total", "unresolved", "resolved", "ignored"]
          : [status];
      await Promise.all(types.map((type) => fetchStatusByType(type, slug)));
    } catch (err) {
      console.error("Error fetching all statuses:", err);
      error.value = `Failed to fetch statuses: ${err.message}`;
      throw error.value;
    }
  };

  return {
    allStatus,
    isLoading,
    error,
    totalIssues,
    unresolvedIssues,
    resolvedIssues,
    ignoredIssues,
    fetchAllStatusApi,
    fetchStatusByType,
    resetState,
  };
});
