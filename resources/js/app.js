import './bootstrap';
import Hero from './Components/Hero.vue'
import HomePage from './Pages/HomePage.vue'
import Login from './Pages/LoginPage.vue'
import Signup from './Pages/SignipPage.vue'
import StudentDashboard from './Pages/student/StudentDashboard.vue'
import MyCourses from './Pages/student/MyCourses.vue'
import ExploreCourses from './Pages/student/ExploreCourses.vue'
import CourseDetail from './Pages/student/CourseDetail.vue'
import LessonView from './Pages/student/LessonView.vue'
import TeacherDashboard from './Pages/teacher/TeacherDashboard.vue'
import TeacherCourses from './Pages/teacher/TeacherCourses.vue'
import TeacherCourseDetail from './Pages/teacher/TeacherCourseDetail.vue'
import TeacherCourseCreate from './Pages/teacher/TeacherCourseCreate.vue'
import { createApp } from 'vue'

const app = createApp({})
app.component('Home', HomePage)
app.component('Login', Login)
app.component('Signup', Signup)
app.component('StudentDashboard', StudentDashboard)
app.component('TeacherDashboard', TeacherDashboard)
app.component('TeacherCourses', TeacherCourses)
app.component('TeacherCourseDetail', TeacherCourseDetail)
app.component('TeacherCourseCreate', TeacherCourseCreate)
app.component('MyCourses', MyCourses)
app.component('ExploreCourses', ExploreCourses)
app.component('CourseDetail', CourseDetail)
app.component('LessonView', LessonView)
app.mount('#app')
