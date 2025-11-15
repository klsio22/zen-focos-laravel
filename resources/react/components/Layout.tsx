import { Outlet } from 'react-router-dom';

export default function Layout() {
  return (
    <div className="flex h-screen bg-slate-200 text-slate-900">
      {/* Sidebar will be added here */}
      <main className="flex-1 overflow-auto">
        <Outlet />
      </main>
    </div>
  );
}
