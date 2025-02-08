import ProjectIssueList from "@/views/ProjectIssueList.vue";
import IssuesPage from "@/views/IssuesPage.vue";
import Dashboard from "@/views/Dashboard.vue";
import RoleControlAccess from "@/views/RoleControlAccess.vue";
import EditUser from "@/views/User/EditUser.vue";
import CreateUser from "@/views/User/CreateUser.vue";
import ListUser from "@/views/User/ListUser.vue";
import DetailIssue from "@/views/DetailIssue.vue";
import LoginPage from "@/views/LoginPage.vue";
import UnknownPage from "@/views/error/UnknownPage.vue";
import DetailProjectIssues from "@/views/DetailProjectIssues.vue";
import ProfileUsers from "@/views/ProfileUsers.vue";
import DetailEventsIssue from "@/views/DetailEventsIssue.vue";
import EditProfile from "@/views/EditProfile.vue";
import ChangePasswordUser from "@/views/User/ChangePasswordUser.vue";
import ChangePasswordProfile from "@/views/ChangePasswordProfile.vue";

const routes = [{
        path: "/",
        name: "login",
        component: LoginPage,
        meta: {
            title: "login",
        },
    },
    {
        path: "/dashboard/:slug?",
        name: "dashboard",
        component: Dashboard,
        meta: {
            requiresAuth: false,
            breadcrumbs: "Dashboard",
            title: "Dashboard",
        },
    },
    {
        path: "/issues",
        name: "Issues",
        component: IssuesPage,
        meta: {
            title: "Issues",
            breadcrumbs: "Issues",
            requiresAuth: false,
        },
    },
    {
        path: "/control-access",
        name: "Control Access",
        component: RoleControlAccess,
        meta: {
            title: "Control Access",
            breadcrumbs: "Control Access",
            requiresAuth: false,
        },
    },
    {
        path: "/Projects",
        name: "Projects",
        component: ProjectIssueList,
        meta: {
            title: "Projects",
            breadcrumbs: "Projects",
            requiresAuth: false,
        },
    },
    {
        path: "/Projects/:slug/issues",
        name: "Detail Project Issue",
        params: true,
        component: DetailProjectIssues,
        meta: {
            title: "Detail Project Issue",
            breadcrumbs: "Detail Project Issue",
            requiresAuth: false,
        },
    },
    {
        path: "/Projects/:slug/issues/:id",
        name: "DetailIssue",
        component: DetailIssue,
        params: true,
        meta: {
            title: "DetailIssue",
            breadcrumbs: "DetailIssue",
            requiresAuth: false,
        },
    },
    {
        path: "/issues/:id",
        name: "DetailIssue",
        component: DetailIssue,
        params: true,
        meta: {
            title: "DetailIssue",
            breadcrumbs: "DetailIssue",
            requiresAuth: false,
        },
    },
    {
        path: "/issues/:id/events",
        name: "DetailEventsIssue",
        component: DetailEventsIssue,
        params: true,
        meta: {
            title: "DetailEventsIssue",
            breadcrumbs: "DetailEventsIssue",
            requiresAuth: false,
        },
    },
    {
        path: "/users-list",
        name: "User List",
        component: ListUser,
        meta: {
            title: "User List",
            breadcrumbs: "User List",
            requiresAuth: false,
        },
    },
    {
        path: "/profile-users",
        name: "ProfileUsers",
        component: ProfileUsers,
        meta: {
            title: "Profile Users",
            breadcrumbs: "Profile Users",
            requiresAuth: false,
        },
    },
    {
        path: "/createuser",
        name: "Create User",
        component: CreateUser,
        meta: {
            title: "Create User",
            breadcrumbs: "Create User",
            requiresAuth: false,
        },
    },
    {
        path: "/update-profile",
        name: "UpdateProfile",
        component: EditProfile,
        meta: {
            title: "Update Profile",
            breadcrumbs: "Update Profile",
            requiresAuth: false,
        }
    },
    {
        path: "/edit-profile/:id",
        name: "Edit Profile",
        component: EditUser,
        meta: {
            title: "Edit Profile",
            breadcrumbs: "Edit Profile",
            requiresAuth: false,
        },
    },
    {
        path: "/change-password-user/:id",
        name: "Change Password User",
        component: ChangePasswordUser,
        meta: {
            title: "Change Password User",
            breadcrumbs: "Change Password User",
            requiresAuth: false,
        },
    },
    {
        path: "/change-password-profile",
        name: "Change Password Profile",
        component: ChangePasswordProfile,
        meta: {
            title: "Change Password Profile",
            breadcrumbs: "Change Password Profile",
            requiresAuth: false,
        },
    },
    {
        path: "/:pathMatch(.*)*",
        name: "404 Error",
        component: UnknownPage,
        meta: {
            title: "404 Error",
            breadcrumbs: "404 Error",
            requiresAuth: false,
        },
    },
];

export default routes;