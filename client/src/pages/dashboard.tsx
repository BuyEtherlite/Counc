import { useQuery } from "@tanstack/react-query";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Fuel, Car, Users, Receipt, TrendingUp, AlertCircle } from "lucide-react";
import { useAuth } from "@/hooks/useAuth";
import { Link } from "wouter";

export default function Dashboard() {
  const { user, isLoading: authLoading } = useAuth();
  
  const { data: stats, isLoading: statsLoading } = useQuery({
    queryKey: ["/api/dashboard/stats"],
  });

  const { data: fuelBalances, isLoading: balancesLoading } = useQuery({
    queryKey: ["/api/fuel-balances"],
    enabled: !!user,
  });

  const { data: myVehicles, isLoading: vehiclesLoading } = useQuery({
    queryKey: ["/api/vehicles/my"],
    enabled: !!user,
  });

  const { data: myTransactions, isLoading: transactionsLoading } = useQuery({
    queryKey: ["/api/transactions/my"],
    enabled: !!user,
  });

  if (authLoading) {
    return <div className="flex items-center justify-center min-h-screen">Loading...</div>;
  }

  if (!user) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <h1 className="text-3xl font-bold mb-4">Fuel Management Platform</h1>
          <p className="text-muted-foreground mb-8">Please sign in to access your dashboard</p>
          <Button asChild>
            <Link href="/signin">Sign In</Link>
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold">Dashboard</h1>
          <p className="text-muted-foreground">
            Welcome back, {user.firstName || user.email}
          </p>
        </div>
        <Badge variant={user.userType === 'admin' ? 'default' : 'secondary'}>
          {user.userType}
        </Badge>
      </div>

      <Tabs defaultValue="overview" className="space-y-6">
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="vehicles">Vehicles</TabsTrigger>
          <TabsTrigger value="transactions">Transactions</TabsTrigger>
          <TabsTrigger value="fuel">Fuel Balance</TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="space-y-6">
          {/* System Stats for Admin */}
          {user.userType === 'admin' && (
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
              {statsLoading ? (
                <div className="col-span-4 text-center">Loading stats...</div>
              ) : (
                <>
                  <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                      <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                      <Users className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                      <div className="text-2xl font-bold">{stats?.totalUsers || 0}</div>
                    </CardContent>
                  </Card>
                  
                  <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                      <CardTitle className="text-sm font-medium">Corporate Fleets</CardTitle>
                      <Car className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                      <div className="text-2xl font-bold">{stats?.corporateFleets || 0}</div>
                    </CardContent>
                  </Card>
                  
                  <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                      <CardTitle className="text-sm font-medium">Pending Vehicles</CardTitle>
                      <AlertCircle className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                      <div className="text-2xl font-bold">{stats?.pendingVehicles || 0}</div>
                    </CardContent>
                  </Card>
                  
                  <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                      <CardTitle className="text-sm font-medium">Monthly Volume</CardTitle>
                      <TrendingUp className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                      <div className="text-2xl font-bold">{stats?.monthlyVolume || 0}L</div>
                    </CardContent>
                  </Card>
                </>
              )}
            </div>
          )}

          {/* Fuel Balances */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Fuel className="h-5 w-5" />
                Your Fuel Balances
              </CardTitle>
            </CardHeader>
            <CardContent>
              {balancesLoading ? (
                <div>Loading balances...</div>
              ) : (
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="flex items-center justify-between p-4 border rounded-lg">
                    <div>
                      <p className="font-medium">Petrol</p>
                      <p className="text-2xl font-bold text-green-600">
                        {fuelBalances?.petrol?.balance || '0.00'}L
                      </p>
                    </div>
                    <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                      <Fuel className="h-6 w-6 text-green-600" />
                    </div>
                  </div>
                  
                  <div className="flex items-center justify-between p-4 border rounded-lg">
                    <div>
                      <p className="font-medium">Diesel</p>
                      <p className="text-2xl font-bold text-blue-600">
                        {fuelBalances?.diesel?.balance || '0.00'}L
                      </p>
                    </div>
                    <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                      <Fuel className="h-6 w-6 text-blue-600" />
                    </div>
                  </div>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Quick Actions */}
          <Card>
            <CardHeader>
              <CardTitle>Quick Actions</CardTitle>
              <CardDescription>Common tasks for fuel management</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 md:grid-cols-3">
                <Button asChild variant="outline">
                  <Link href="/vehicles/register">Register Vehicle</Link>
                </Button>
                <Button asChild variant="outline">
                  <Link href="/coupons">Redeem Coupon</Link>
                </Button>
                <Button asChild variant="outline">
                  <Link href="/transactions/new">Make Transaction</Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="vehicles" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>My Vehicles</CardTitle>
              <CardDescription>Manage your registered vehicles</CardDescription>
            </CardHeader>
            <CardContent>
              {vehiclesLoading ? (
                <div>Loading vehicles...</div>
              ) : myVehicles?.length === 0 ? (
                <div className="text-center py-8">
                  <Car className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground mb-4">No vehicles registered yet</p>
                  <Button asChild>
                    <Link href="/vehicles/register">Register Your First Vehicle</Link>
                  </Button>
                </div>
              ) : (
                <div className="space-y-4">
                  {myVehicles?.map((vehicle: any) => (
                    <div key={vehicle.id} className="flex items-center justify-between p-4 border rounded-lg">
                      <div>
                        <p className="font-medium">{vehicle.registrationNumber}</p>
                        <p className="text-sm text-muted-foreground">
                          {vehicle.make} {vehicle.model} ({vehicle.year}) - {vehicle.fuelType}
                        </p>
                      </div>
                      <Badge variant={
                        vehicle.status === 'approved' ? 'default' :
                        vehicle.status === 'pending' ? 'secondary' : 'destructive'
                      }>
                        {vehicle.status}
                      </Badge>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="transactions" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Recent Transactions</CardTitle>
              <CardDescription>Your fuel transaction history</CardDescription>
            </CardHeader>
            <CardContent>
              {transactionsLoading ? (
                <div>Loading transactions...</div>
              ) : myTransactions?.length === 0 ? (
                <div className="text-center py-8">
                  <Receipt className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground">No transactions yet</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {myTransactions?.map((transaction: any) => (
                    <div key={transaction.id} className="flex items-center justify-between p-4 border rounded-lg">
                      <div>
                        <p className="font-medium">{transaction.transactionType.replace('_', ' ')}</p>
                        <p className="text-sm text-muted-foreground">
                          {transaction.fuelType} - {transaction.amount}L
                        </p>
                        <p className="text-xs text-muted-foreground">
                          {new Date(transaction.createdAt).toLocaleDateString()}
                        </p>
                      </div>
                      <Badge variant={
                        transaction.status === 'completed' ? 'default' :
                        transaction.status === 'pending' ? 'secondary' : 'destructive'
                      }>
                        {transaction.status}
                      </Badge>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="fuel" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Fuel Balance Management</CardTitle>
              <CardDescription>Top up your fuel balance and redeem coupons</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid gap-6 md:grid-cols-2">
                <div>
                  <h3 className="font-medium mb-4">Current Balances</h3>
                  <div className="space-y-4">
                    <div className="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                      <span>Petrol</span>
                      <span className="font-bold text-green-600">
                        {fuelBalances?.petrol?.balance || '0.00'}L
                      </span>
                    </div>
                    <div className="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                      <span>Diesel</span>
                      <span className="font-bold text-blue-600">
                        {fuelBalances?.diesel?.balance || '0.00'}L
                      </span>
                    </div>
                  </div>
                </div>
                
                <div>
                  <h3 className="font-medium mb-4">Quick Actions</h3>
                  <div className="space-y-3">
                    <Button asChild className="w-full">
                      <Link href="/fuel/topup">Top Up Balance</Link>
                    </Button>
                    <Button asChild variant="outline" className="w-full">
                      <Link href="/coupons">Redeem Coupon</Link>
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}