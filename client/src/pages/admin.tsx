import { useState } from "react";
import { useQuery, useMutation } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Textarea } from "@/components/ui/textarea";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { 
  Users, 
  Car, 
  Building2, 
  Ticket, 
  CreditCard, 
  Shield, 
  Settings, 
  CheckCircle, 
  XCircle,
  Clock,
  FileText,
  DollarSign,
  TrendingUp
} from "lucide-react";
import { useAuth } from "@/hooks/useAuth";
import { useToast } from "@/hooks/use-toast";
import { queryClient, apiRequest } from "@/lib/queryClient";

// Admin Dashboard - Comprehensive fuel management platform administration
export default function Admin() {
  const { user } = useAuth();
  const { toast } = useToast();
  const [activeTab, setActiveTab] = useState("overview");

  // Queries for dashboard data
  const { data: stats, isLoading: statsLoading } = useQuery({
    queryKey: ["/api/dashboard/stats"],
    enabled: user?.userType === 'admin',
  });

  const { data: pendingVehicles, isLoading: vehiclesLoading } = useQuery({
    queryKey: ["/api/vehicles/pending"],
    enabled: user?.userType === 'admin',
  });

  const { data: allUsers, isLoading: usersLoading } = useQuery({
    queryKey: ["/api/admin/users"],
    enabled: user?.userType === 'admin',
  });

  const { data: coupons, isLoading: couponsLoading } = useQuery({
    queryKey: ["/api/coupons/active"],
    enabled: user?.userType === 'admin',
  });

  const { data: merchants, isLoading: merchantsLoading } = useQuery({
    queryKey: ["/api/merchants"],
    enabled: user?.userType === 'admin',
  });

  const { data: withdrawalRequests, isLoading: withdrawalsLoading } = useQuery({
    queryKey: ["/api/withdrawals/pending"],
    enabled: user?.userType === 'admin',
  });

  // Mutations for admin actions
  const approveVehicleMutation = useMutation({
    mutationFn: (vehicleId: string) => apiRequest(`/api/vehicles/${vehicleId}/approve`, "POST"),
    onSuccess: () => {
      toast({
        title: "Vehicle approved",
        description: "The vehicle has been approved successfully.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/vehicles/pending"] });
    },
  });

  const rejectVehicleMutation = useMutation({
    mutationFn: ({ vehicleId, reason }: { vehicleId: string; reason: string }) => 
      apiRequest(`/api/vehicles/${vehicleId}/reject`, "POST", { reason }),
    onSuccess: () => {
      toast({
        title: "Vehicle rejected",
        description: "The vehicle has been rejected.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/vehicles/pending"] });
    },
  });

  const updateUserStatusMutation = useMutation({
    mutationFn: ({ userId, status }: { userId: string; status: string }) => 
      apiRequest(`/api/admin/users/${userId}/status`, "PATCH", { status }),
    onSuccess: () => {
      toast({
        title: "User status updated",
        description: "The user status has been updated successfully.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/admin/users"] });
    },
  });

  if (!user || user.userType !== 'admin') {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <Shield className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
          <h1 className="text-3xl font-bold mb-4">Access Denied</h1>
          <p className="text-muted-foreground">You need administrator privileges to access this page.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold">Admin Dashboard</h1>
          <p className="text-muted-foreground">Comprehensive fuel management platform administration</p>
        </div>
        <Badge variant="default" className="flex items-center gap-2">
          <Shield className="h-4 w-4" />
          Administrator
        </Badge>
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
        <TabsList className="grid w-full grid-cols-7">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="users">Users</TabsTrigger>
          <TabsTrigger value="vehicles">Vehicles</TabsTrigger>
          <TabsTrigger value="coupons">Coupons</TabsTrigger>
          <TabsTrigger value="merchants">Merchants</TabsTrigger>
          <TabsTrigger value="withdrawals">Withdrawals</TabsTrigger>
          <TabsTrigger value="settings">Settings</TabsTrigger>
        </TabsList>

        {/* Overview Tab */}
        <TabsContent value="overview" className="space-y-6">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            {statsLoading ? (
              <div className="col-span-4 text-center">Loading statistics...</div>
            ) : (
              <>
                <Card>
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                    <Users className="h-4 w-4 text-muted-foreground" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">{stats?.totalUsers || 0}</div>
                    <p className="text-xs text-muted-foreground">All registered users</p>
                  </CardContent>
                </Card>
                
                <Card>
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium">Corporate Fleets</CardTitle>
                    <Building2 className="h-4 w-4 text-muted-foreground" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">{stats?.corporateFleets || 0}</div>
                    <p className="text-xs text-muted-foreground">Active fleet accounts</p>
                  </CardContent>
                </Card>
                
                <Card>
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium">Pending Vehicles</CardTitle>
                    <Car className="h-4 w-4 text-muted-foreground" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">{stats?.pendingVehicles || 0}</div>
                    <p className="text-xs text-muted-foreground">Awaiting approval</p>
                  </CardContent>
                </Card>
                
                <Card>
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium">Monthly Revenue</CardTitle>
                    <DollarSign className="h-4 w-4 text-muted-foreground" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">${stats?.totalRevenue || 0}</div>
                    <p className="text-xs text-muted-foreground">This month</p>
                  </CardContent>
                </Card>
              </>
            )}
          </div>

          {/* Recent Activity */}
          <div className="grid gap-6 lg:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Recent Vehicles Pending Approval</CardTitle>
                <CardDescription>Vehicles requiring administrator review</CardDescription>
              </CardHeader>
              <CardContent>
                {vehiclesLoading ? (
                  <div>Loading vehicles...</div>
                ) : pendingVehicles?.length === 0 ? (
                  <p className="text-muted-foreground text-center py-4">No pending vehicles</p>
                ) : (
                  <div className="space-y-4">
                    {pendingVehicles?.slice(0, 5).map((vehicle: any) => (
                      <div key={vehicle.id} className="flex items-center justify-between p-3 border rounded-lg">
                        <div>
                          <p className="font-medium">{vehicle.registrationNumber}</p>
                          <p className="text-sm text-muted-foreground">{vehicle.make} {vehicle.model}</p>
                        </div>
                        <div className="flex gap-2">
                          <Button
                            size="sm"
                            onClick={() => approveVehicleMutation.mutate(vehicle.id)}
                            disabled={approveVehicleMutation.isPending}
                          >
                            <CheckCircle className="h-4 w-4" />
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => rejectVehicleMutation.mutate({ vehicleId: vehicle.id, reason: "Documentation required" })}
                            disabled={rejectVehicleMutation.isPending}
                          >
                            <XCircle className="h-4 w-4" />
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>System Status</CardTitle>
                <CardDescription>Platform health and performance metrics</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center justify-between">
                  <span>Database Connection</span>
                  <Badge variant="default">Online</Badge>
                </div>
                <div className="flex items-center justify-between">
                  <span>Payment Gateway</span>
                  <Badge variant="default">Connected</Badge>
                </div>
                <div className="flex items-center justify-between">
                  <span>Active Coupons</span>
                  <Badge variant="secondary">{stats?.activeCoupons || 0}</Badge>
                </div>
                <div className="flex items-center justify-between">
                  <span>Monthly Fuel Volume</span>
                  <span className="font-medium">{stats?.monthlyVolume || 0}L</span>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {/* Users Management Tab */}
        <TabsContent value="users" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>User Management</CardTitle>
              <CardDescription>View and manage all platform users</CardDescription>
            </CardHeader>
            <CardContent>
              {usersLoading ? (
                <div>Loading users...</div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Name</TableHead>
                      <TableHead>Email</TableHead>
                      <TableHead>Type</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {allUsers?.map((user: any) => (
                      <TableRow key={user.id}>
                        <TableCell>
                          {user.firstName} {user.lastName}
                        </TableCell>
                        <TableCell>{user.email}</TableCell>
                        <TableCell>
                          <Badge variant="outline">{user.userType}</Badge>
                        </TableCell>
                        <TableCell>
                          <Badge variant={user.status === 'active' ? 'default' : 'secondary'}>
                            {user.status || 'active'}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <div className="flex gap-2">
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => updateUserStatusMutation.mutate({ 
                                userId: user.id, 
                                status: user.status === 'active' ? 'suspended' : 'active' 
                              })}
                              disabled={updateUserStatusMutation.isPending}
                            >
                              {user.status === 'active' ? 'Suspend' : 'Activate'}
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Vehicle Approval Tab */}
        <TabsContent value="vehicles" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Vehicle Approval Queue</CardTitle>
              <CardDescription>Review and approve vehicle registrations</CardDescription>
            </CardHeader>
            <CardContent>
              {vehiclesLoading ? (
                <div>Loading vehicles...</div>
              ) : pendingVehicles?.length === 0 ? (
                <div className="text-center py-8">
                  <Car className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground">No vehicles pending approval</p>
                </div>
              ) : (
                <div className="space-y-4">
                  {pendingVehicles?.map((vehicle: any) => (
                    <Card key={vehicle.id}>
                      <CardContent className="p-6">
                        <div className="grid gap-4 md:grid-cols-2">
                          <div>
                            <h3 className="font-semibold mb-2">{vehicle.registrationNumber}</h3>
                            <div className="space-y-1 text-sm">
                              <p><span className="font-medium">Make/Model:</span> {vehicle.make} {vehicle.model}</p>
                              <p><span className="font-medium">Year:</span> {vehicle.year}</p>
                              <p><span className="font-medium">Fuel Type:</span> {vehicle.fuelType}</p>
                              <p><span className="font-medium">Owner:</span> {vehicle.ownerName}</p>
                              <p><span className="font-medium">Submitted:</span> {new Date(vehicle.createdAt).toLocaleDateString()}</p>
                            </div>
                          </div>
                          <div className="flex flex-col justify-between">
                            <div>
                              <h4 className="font-medium mb-2">Documents</h4>
                              <div className="space-y-1">
                                <p className="text-sm text-muted-foreground">
                                  <FileText className="h-4 w-4 inline mr-2" />
                                  Registration Document
                                </p>
                              </div>
                            </div>
                            <div className="flex gap-2 mt-4">
                              <Button
                                onClick={() => approveVehicleMutation.mutate(vehicle.id)}
                                disabled={approveVehicleMutation.isPending}
                                className="flex-1"
                              >
                                <CheckCircle className="h-4 w-4 mr-2" />
                                Approve
                              </Button>
                              <Button
                                variant="outline"
                                onClick={() => rejectVehicleMutation.mutate({ 
                                  vehicleId: vehicle.id, 
                                  reason: "Documentation incomplete" 
                                })}
                                disabled={rejectVehicleMutation.isPending}
                                className="flex-1"
                              >
                                <XCircle className="h-4 w-4 mr-2" />
                                Reject
                              </Button>
                            </div>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Coupon Management Tab */}
        <TabsContent value="coupons" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Coupon Management</CardTitle>
              <CardDescription>Create and manage fuel coupons</CardDescription>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground mb-4">
                Use the dedicated Coupons page to create and manage fuel coupons.
              </p>
              <Button asChild>
                <a href="/coupons">Go to Coupons</a>
              </Button>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Merchant Management Tab */}
        <TabsContent value="merchants" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Merchant Management</CardTitle>
              <CardDescription>Manage fuel stations and service providers</CardDescription>
            </CardHeader>
            <CardContent>
              {merchantsLoading ? (
                <div>Loading merchants...</div>
              ) : merchants?.length === 0 ? (
                <div className="text-center py-8">
                  <Building2 className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground">No merchants registered</p>
                </div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Station Name</TableHead>
                      <TableHead>Address</TableHead>
                      <TableHead>Contact</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Balance</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {merchants?.map((merchant: any) => (
                      <TableRow key={merchant.id}>
                        <TableCell className="font-medium">{merchant.stationName}</TableCell>
                        <TableCell>{merchant.address}</TableCell>
                        <TableCell>{merchant.contactPhone}</TableCell>
                        <TableCell>
                          <Badge variant={merchant.status === 'active' ? 'default' : 'secondary'}>
                            {merchant.status}
                          </Badge>
                        </TableCell>
                        <TableCell>${merchant.pendingBalance}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Withdrawal Requests Tab */}
        <TabsContent value="withdrawals" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Withdrawal Requests</CardTitle>
              <CardDescription>Process merchant withdrawal requests</CardDescription>
            </CardHeader>
            <CardContent>
              {withdrawalsLoading ? (
                <div>Loading withdrawal requests...</div>
              ) : withdrawalRequests?.length === 0 ? (
                <div className="text-center py-8">
                  <CreditCard className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground">No pending withdrawal requests</p>
                </div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Merchant</TableHead>
                      <TableHead>Amount</TableHead>
                      <TableHead>Requested</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {withdrawalRequests?.map((request: any) => (
                      <TableRow key={request.id}>
                        <TableCell>{request.merchantName}</TableCell>
                        <TableCell>${request.amount}</TableCell>
                        <TableCell>{new Date(request.requestedAt).toLocaleDateString()}</TableCell>
                        <TableCell>
                          <Badge variant="secondary">{request.status}</Badge>
                        </TableCell>
                        <TableCell>
                          <div className="flex gap-2">
                            <Button size="sm">Approve</Button>
                            <Button size="sm" variant="outline">Reject</Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Settings Tab */}
        <TabsContent value="settings" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>System Settings</CardTitle>
              <CardDescription>Configure platform settings and permissions</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div>
                <h3 className="font-semibold mb-2">Fuel Pricing</h3>
                <div className="grid gap-4 md:grid-cols-2">
                  <div>
                    <label className="text-sm font-medium">Petrol Price (per liter)</label>
                    <Input placeholder="$1.50" />
                  </div>
                  <div>
                    <label className="text-sm font-medium">Diesel Price (per liter)</label>
                    <Input placeholder="$1.40" />
                  </div>
                </div>
              </div>

              <div>
                <h3 className="font-semibold mb-2">Transaction Limits</h3>
                <div className="grid gap-4 md:grid-cols-3">
                  <div>
                    <label className="text-sm font-medium">Daily Purchase Limit</label>
                    <Input placeholder="$100" />
                  </div>
                  <div>
                    <label className="text-sm font-medium">Monthly Purchase Limit</label>
                    <Input placeholder="$2500" />
                  </div>
                  <div>
                    <label className="text-sm font-medium">Daily Transfer Limit</label>
                    <Input placeholder="$50" />
                  </div>
                </div>
              </div>

              <Button>Save Settings</Button>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}