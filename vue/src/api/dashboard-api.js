import api from "./api";

// dashboard api
export const AllStatusApi = (type, slug) =>
  api.get(`/dashboard/issues/${type}/${slug}`);
