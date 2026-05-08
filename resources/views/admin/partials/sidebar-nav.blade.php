<nav class="admin-sidebar-scroll flex-1 overflow-y-auto px-4 py-6 space-y-1">
    <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 @if(request()->routeIs('admin.dashboard')) bg-blue-600 text-white shadow-lg shadow-blue-200 @else text-slate-600 hover:bg-slate-50 hover:text-blue-600 @endif">
       
        Dashboard
    </a>

    @if(Auth::user()->hasPermission('visits'))
    <a href="{{ route('admin.visits') }}" class="group flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 @if(request()->routeIs('admin.visits')) bg-blue-600 text-white shadow-lg shadow-blue-200 @else text-slate-600 hover:bg-slate-50 hover:text-blue-600 @endif">
        
        Visitor Logs
    </a>
    @endif
    
    @if(Auth::user()->hasPermission('transactions') || Auth::user()->hasPermission('pricing'))
    <div class="pt-6 pb-2">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Financials</p>
    </div>
    @if(Auth::user()->hasPermission('transactions'))
    <a href="{{ route('admin.transactions') }}" class="group flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 @if(request()->routeIs('admin.transactions')) bg-blue-600 text-white shadow-lg shadow-blue-200 @else text-slate-600 hover:bg-slate-50 hover:text-blue-600 @endif">
        
        Transactions
    </a>
    @endif
    @if(Auth::user()->hasPermission('pricing'))
    <a href="{{ route('admin.payments') }}" class="group flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 @if(request()->routeIs('admin.payments')) bg-blue-600 text-white shadow-lg shadow-blue-200 @else text-slate-600 hover:bg-slate-50 hover:text-blue-600 @endif">
       
        Pricing Plans
    </a>
    @endif
    @endif

    @if(Auth::user()->hasPermission('templates') || Auth::user()->hasPermission('articles'))
    <div class="pt-6 pb-2">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Content</p>
    </div>
    @if(Auth::user()->hasPermission('templates'))
    <a href="{{ route('admin.templates.index') }}" class="group flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 @if(request()->routeIs('admin.templates.*')) bg-blue-600 text-white shadow-lg shadow-blue-200 @else text-slate-600 hover:bg-slate-50 hover:text-blue-600 @endif">
       
        Templates
    </a>
    @endif
    @if(Auth::user()->hasPermission('articles'))
    <a href="{{ route('admin.articles.index') }}" class="group flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 @if(request()->routeIs('admin.articles.*')) bg-blue-600 text-white shadow-lg shadow-blue-200 @else text-slate-600 hover:bg-slate-50 hover:text-blue-600 @endif">
        
        Articles
    </a>
    @endif
    @endif

    @if(Auth::user()->hasPermission('team'))
    <div class="pt-6 pb-2">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Administration</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="group flex items-center px-4 py-3 rounded-xl font-medium transition-all duration-200 @if(request()->routeIs('admin.users.*')) bg-blue-600 text-white shadow-lg shadow-blue-200 @else text-slate-600 hover:bg-slate-50 hover:text-blue-600 @endif">
        
        Team Management
    </a>
    @endif
</nav>
