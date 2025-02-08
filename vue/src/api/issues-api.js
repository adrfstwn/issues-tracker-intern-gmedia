import api from "./api";
// detail all issue api
export const allListIssuesApi = (page = 1) => api.get(`/issues?page=${page}`);

// detail issue api
export const detailIssuesApi = (id) => api.get(`/issues/${id}`);

//detail status issue api
export const detailStatusIssuesApi = (id, page = 1) =>
  api.get(`/issues/${id}/status?page=${page}`);

// detail events issue api
export const detailEventsIssuesApi = (id, page = 1) =>
  api.get(`/issues/${id}/events?page=${page}`);

// track trace issue api
export const detailTraceIssuesApi = (id) => api.get(`/issues/${id}/trace`);
// edit status issue api
export const editStatusIssueApi = (id, data) =>
  api.put(`/issues/${id}/status`, data);

// create issue to work packages api
export const createWorkPackagesApi = (id, data) =>
  api.post(`/issues/${id}/post`, data);

// update issue to work packages api
export const updateIssueWorkPackagesApi = (id, data) =>
  api.patch(`/work-package/${id}`, data);

// data user has assignee api
export const userAssigneeeApi = () => api.get(`/assignees`);

//has data assignee api
export const dataAssigneedApi = () => api.get(`/openproject`);

//  api root projects
export const rootProjectsApi = () => api.get(`/openproject/projects`);
