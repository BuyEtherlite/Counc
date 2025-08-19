import { useLocation } from "wouter";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Fuel, LogIn } from "lucide-react";


export default function SignIn() {
  const [, setLocation] = useLocation();

  // In development mode, automatically go to dashboard
  const handleSignIn = async () => {
    if (import.meta.env.DEV) {
      // Create or access the development user first, then redirect
      try {
        const response = await fetch("/api/auth/user");
        if (response.ok || response.status === 404) {
          // Either user exists or will be created by the mock middleware
          setLocation("/dashboard");
        }
      } catch (error) {
        // Even if there's an error, redirect in dev mode
        setLocation("/dashboard");
      }
    } else {
      // In production, redirect to Replit auth
      window.location.href = "/api/login";
    }
  };



  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center p-4">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center space-y-2">
          <div className="flex justify-center">
            <div className="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
              <Fuel className="h-8 w-8 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
          <CardTitle className="text-2xl font-bold">Fuel Management Platform</CardTitle>
          <CardDescription>
            Sign in to manage your fleet, track fuel usage, and monitor transactions
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <Button onClick={handleSignIn} className="w-full" size="lg">
            <LogIn className="mr-2 h-4 w-4" />
            {import.meta.env.DEV ? "Continue to Dashboard" : "Sign in with Replit"}
          </Button>
          
          {import.meta.env.DEV && (
            <div className="text-center text-sm text-muted-foreground">
              Development mode - Using mock authentication
            </div>
          )}
          
          <div className="text-center text-xs text-muted-foreground space-y-1">
            <p>Features available:</p>
            <ul className="list-disc list-inside space-y-0.5">
              <li>Fleet vehicle management</li>
              <li>Fuel balance tracking</li>
              <li>Coupon system</li>
              <li>Transaction monitoring</li>
              <li>Admin controls</li>
            </ul>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}