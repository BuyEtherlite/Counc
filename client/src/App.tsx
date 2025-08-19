import { Switch, Route } from "wouter";
import { queryClient } from "./lib/queryClient";
import { QueryClientProvider } from "@tanstack/react-query";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import Dashboard from "@/pages/dashboard";
import Vehicles from "@/pages/vehicles";
import Coupons from "@/pages/coupons";
import Transactions from "@/pages/transactions";
import Admin from "@/pages/admin";
import Fleet from "@/pages/fleet";
import SignIn from "@/pages/signin";
import NotFound from "@/pages/not-found";

function Router() {
  return (
    <Switch>
      <Route path="/" component={SignIn} />
      <Route path="/signin" component={SignIn} />
      <Route path="/login" component={SignIn} />
      <Route path="/dashboard" component={Dashboard} />
      <Route path="/vehicles" component={Vehicles} />
      <Route path="/vehicles/:tab" component={Vehicles} />
      <Route path="/coupons" component={Coupons} />
      <Route path="/transactions" component={Transactions} />
      <Route path="/transactions/new" component={Transactions} />
      <Route path="/admin" component={Admin} />
      <Route path="/imagine" component={Admin} />
      <Route path="/fleet" component={Fleet} />
      {/* Fallback to 404 */}
      <Route component={NotFound} />
    </Switch>
  );
}

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <div className="min-h-screen bg-background">
          <Router />
          <Toaster />
        </div>
      </TooltipProvider>
    </QueryClientProvider>
  );
}

export default App;
