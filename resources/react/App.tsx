import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Layout from './components/Layout';
import Home from './pages/Home';
import TasksIndex from './pages/TasksIndex';
import TaskCreate from './pages/TaskCreate';
import TaskEdit from './pages/TaskEdit';
import FocusedTimer from './pages/FocusedTimer';

function App() {
  return (
    <Router>
      <Routes>
        <Route element={<Layout />}>
          <Route path="/" element={<Home />} />
          <Route path="/tasks" element={<TasksIndex />} />
          <Route path="/tasks/create" element={<TaskCreate />} />
          <Route path="/tasks/:id/edit" element={<TaskEdit />} />
          <Route path="/tasks/:id/timer" element={<FocusedTimer />} />
          <Route path="*" element={<Navigate to="/" />} />
        </Route>
      </Routes>
    </Router>
  );
}

export default App;
