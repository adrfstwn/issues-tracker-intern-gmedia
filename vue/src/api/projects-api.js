import api from "./api";
// project list api
export const projectsListApi = (page = 1) => api.get(`/projects?page=${page}`);

// proejct list issue api
export const projectsListIssueApi = (slug, page = 1) =>
  api.get(`/projects/${slug}/issues?page=${page}`);

// project event api
export const projectsListEventApi = (slug, page = 1) =>
  api.get(`/projects/${slug}/events?page=${page}`);

// root project api
  export const saveRootProjectsApi = (slug, data) => api.post(`/root-project/${slug}`, data);

