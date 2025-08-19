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
  Building2, 
  Users, 
  Car, 
  Plus, 
  Settings, 
  TrendingUp,
  UserPlus,
  Fuel,
  BarChart3,
  Shield
} from "lucide-react";
import { useAuth } from "@/hooks/useAuth";
import { useToast } from "@/hooks/use-toast";
import { queryClient, apiRequest } from "@/lib/queryClient";

// Fleet Management - Corporate fuel management interface
const driverSchema = z.object({
  firstName: z.string().min(1, "First name is required"),
  lastName: z.string().min(1, "Last name is required"),
  email: z.string().email("Valid email is required"),
  phone: z.string().optional(),
  licenseNumber: z.string().min(1, "License number is required"),
  fuelLimit: z.string().min(1, "Fuel limit is required"),
});

const companySchema = z.object({
  name: z.string().min(1, "Company name is required"),
  registrationNumber: z.string().min(1, "Registration number is required"),
  address: z.string().min(1, "Address is required"),
  contactEmail: z.string().email("Valid email is required"),
  contactPhone: z.string().min(1, "Contact phone is required"),
});

type DriverFormData = z.infer<typeof driverSchema>;
type CompanyFormData = z.infer<typeof companySchema>;

export default function Fleet() {
  const { user } = useAuth();
  const { toast } = useToast();
  const [activeTab, setActiveTab] = useState("overview");
  const [showAddDriver, setShowAddDriver] = useState(false);
  const [showCompanySetup, setShowCompanySetup] = useState(false);

  // Queries for fleet data
  const { data: company, isLoading: companyLoading } = useQuery({
    queryKey: ["/api/company/profile"],
    enabled: user?.userType === 'corporate',
  });

  const { data: drivers, isLoading: driversLoading } = useQuery({
    queryKey: ["/api/fleet/drivers"],
    enabled: user?.userType === 'corporate',
  });

  const { data: vehicles, isLoading: vehiclesLoading } = useQuery({
    queryKey: ["/api/fleet/vehicles"],
    enabled: user?.userType === 'corporate',
  });

  const { data: fuelStats, isLoading: statsLoading } = useQuery({
    queryKey: ["/api/fleet/fuel-stats"],
    enabled: user?.userType === 'corporate',
  });

  const { data: transactions, isLoading: transactionsLoading } = useQuery({
    queryKey: ["/api/fleet/transactions"],
    enabled: user?.userType === 'corporate',
  });

  // Forms
  const driverForm = useForm<DriverFormData>({
    resolver: zodResolver(driverSchema),
    defaultValues: {
      firstName: "",
      lastName: "",
      email: "",
      phone: "",
      licenseNumber: "",
      fuelLimit: "",
    },
  });

  const companyForm = useForm<CompanyFormData>({
    resolver: zodResolver(companySchema),
    defaultValues: {
      name: "",
      registrationNumber: "",
      address: "",
      contactEmail: "",
      contactPhone: "",
    },
  });

  // Mutations
  const createDriverMutation = useMutation({
    mutationFn: (data: DriverFormData) => apiRequest("/api/fleet/drivers", "POST", data),
    onSuccess: () => {
      toast({
        title: "Driver added successfully",
        description: "The new driver has been added to your fleet.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/fleet/drivers"] });
      driverForm.reset();
      setShowAddDriver(false);
    },
  });

  const setupCompanyMutation = useMutation({
    mutationFn: (data: CompanyFormData) => apiRequest("/api/company/setup", "POST", data),
    onSuccess: () => {
      toast({
        title: "Company setup completed",
        description: "Your corporate account has been configured successfully.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/company/profile"] });
      companyForm.reset();
      setShowCompanySetup(false);
    },
  });

  const updateDriverLimitMutation = useMutation({
    mutationFn: ({ driverId, limit }: { driverId: string; limit: string }) => 
      apiRequest(`/api/fleet/drivers/${driverId}/limit`, "PATCH", { fuelLimit: limit }),
    onSuccess: () => {
      toast({
        title: "Driver limit updated",
        description: "The fuel limit has been updated successfully.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/fleet/drivers"] });
    },
  });

  if (!user) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <h1 className="text-3xl font-bold mb-4">Fleet Management</h1>
          <p className="text-muted-foreground">Please sign in to access fleet management</p>
        </div>
      </div>
    );
  }

  // Show company setup if no company profile exists
  if (user.userType === 'corporate' && !company && !companyLoading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <Card className="max-w-2xl mx-auto">
          <CardHeader className="text-center">
            <Building2 className="h-12 w-12 text-blue-600 mx-auto mb-4" />
            <CardTitle>Welcome to Fleet Management</CardTitle>
            <CardDescription>
              Set up your corporate account to start managing your fleet
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Form {...companyForm}>
              <form onSubmit={companyForm.handleSubmit((data) => setupCompanyMutation.mutate(data))} className="space-y-4">
                <FormField
                  control={companyForm.control}
                  name="name"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Company Name</FormLabel>
                      <FormControl>
                        <Input placeholder="Acme Corporation" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={companyForm.control}
                  name="registrationNumber"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Registration Number</FormLabel>
                      <FormControl>
                        <Input placeholder="12345678" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={companyForm.control}
                  name="address"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Company Address</FormLabel>
                      <FormControl>
                        <Input placeholder="123 Business St, City, State" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={companyForm.control}
                  name="contactEmail"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Contact Email</FormLabel>
                      <FormControl>
                        <Input type="email" placeholder="contact@company.com" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={companyForm.control}
                  name="contactPhone"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Contact Phone</FormLabel>
                      <FormControl>
                        <Input placeholder="+1234567890" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <Button 
                  type="submit" 
                  className="w-full"
                  disabled={setupCompanyMutation.isPending}
                >
                  {setupCompanyMutation.isPending ? "Setting up..." : "Complete Setup"}
                </Button>
              </form>
            </Form>
          </CardContent>
        </Card>
      </div>
    );
  }

  if (user.userType !== 'corporate') {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <Shield className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
          <h1 className="text-3xl font-bold mb-4">Fleet Management</h1>
          <p className="text-muted-foreground">
            This feature is only available for corporate accounts.
          </p>
          <p className="text-sm text-muted-foreground mt-2">
            Contact support to upgrade your account to corporate.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold">Fleet Management</h1>
          <p className="text-muted-foreground">
            {company ? `Managing ${company.name}` : "Corporate fuel management dashboard"}
          </p>
        </div>
        <div className="flex gap-2">
          <Dialog open={showAddDriver} onOpenChange={setShowAddDriver}>
            <DialogTrigger asChild>
              <Button>
                <UserPlus className="h-4 w-4 mr-2" />
                Add Driver
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Add New Driver</DialogTitle>
                <DialogDescription>
                  Add a new driver to your corporate fleet
                </DialogDescription>
              </DialogHeader>
              <Form {...driverForm}>
                <form onSubmit={driverForm.handleSubmit((data) => createDriverMutation.mutate(data))} className="space-y-4">
                  <div className="grid gap-4 md:grid-cols-2">
                    <FormField
                      control={driverForm.control}
                      name="firstName"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>First Name</FormLabel>
                          <FormControl>
                            <Input placeholder="John" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <FormField
                      control={driverForm.control}
                      name="lastName"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Last Name</FormLabel>
                          <FormControl>
                            <Input placeholder="Smith" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>

                  <FormField
                    control={driverForm.control}
                    name="email"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Email</FormLabel>
                        <FormControl>
                          <Input type="email" placeholder="john.smith@company.com" {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={driverForm.control}
                    name="phone"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Phone (Optional)</FormLabel>
                        <FormControl>
                          <Input placeholder="+1234567890" {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={driverForm.control}
                    name="licenseNumber"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>License Number</FormLabel>
                        <FormControl>
                          <Input placeholder="DL123456" {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={driverForm.control}
                    name="fuelLimit"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Monthly Fuel Limit (Liters)</FormLabel>
                        <FormControl>
                          <Input type="number" placeholder="500" {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <Button 
                    type="submit" 
                    className="w-full"
                    disabled={createDriverMutation.isPending}
                  >
                    {createDriverMutation.isPending ? "Adding..." : "Add Driver"}
                  </Button>
                </form>
              </Form>
            </DialogContent>
          </Dialog>
        </div>
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
        <TabsList className="grid w-full grid-cols-5">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="drivers">Drivers</TabsTrigger>
          <TabsTrigger value="vehicles">Vehicles</TabsTrigger>
          <TabsTrigger value="fuel">Fuel Usage</TabsTrigger>
          <TabsTrigger value="reports">Reports</TabsTrigger>
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
                    <CardTitle className="text-sm font-medium">Total Drivers</CardTitle>
                    <Users className="h-4 w-4 text-muted-foreground" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">{fuelStats?.totalDrivers || 0}</div>
                    <p className="text-xs text-muted-foreground">Active fleet drivers</p>
                  </CardContent>
                </Card>
                
                <Card>
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium">Fleet Vehicles</CardTitle>
                    <Car className="h-4 w-4 text-muted-foreground" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">{fuelStats?.totalVehicles || 0}</div>
                    <p className="text-xs text-muted-foreground">Registered vehicles</p>
                  </CardContent>
                </Card>
                
                <Card>
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium">Monthly Usage</CardTitle>
                    <Fuel className="h-4 w-4 text-muted-foreground" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">{fuelStats?.monthlyUsage || 0}L</div>
                    <p className="text-xs text-muted-foreground">This month</p>
                  </CardContent>
                </Card>
                
                <Card>
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle className="text-sm font-medium">Fuel Balance</CardTitle>
                    <TrendingUp className="h-4 w-4 text-muted-foreground" />
                  </CardHeader>
                  <CardContent>
                    <div className="text-2xl font-bold">{fuelStats?.totalBalance || 0}L</div>
                    <p className="text-xs text-muted-foreground">Available fuel</p>
                  </CardContent>
                </Card>
              </>
            )}
          </div>

          {/* Company Info */}
          {company && (
            <Card>
              <CardHeader>
                <CardTitle>Company Information</CardTitle>
                <CardDescription>Corporate account details</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid gap-4 md:grid-cols-2">
                  <div>
                    <p className="text-sm font-medium">Company Name</p>
                    <p className="text-muted-foreground">{company.name}</p>
                  </div>
                  <div>
                    <p className="text-sm font-medium">Registration Number</p>
                    <p className="text-muted-foreground">{company.registrationNumber}</p>
                  </div>
                  <div>
                    <p className="text-sm font-medium">Address</p>
                    <p className="text-muted-foreground">{company.address}</p>
                  </div>
                  <div>
                    <p className="text-sm font-medium">Contact</p>
                    <p className="text-muted-foreground">{company.contactEmail}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}
        </TabsContent>

        {/* Drivers Tab */}
        <TabsContent value="drivers" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Fleet Drivers</CardTitle>
              <CardDescription>Manage your fleet drivers and their fuel limits</CardDescription>
            </CardHeader>
            <CardContent>
              {driversLoading ? (
                <div>Loading drivers...</div>
              ) : drivers?.length === 0 ? (
                <div className="text-center py-8">
                  <Users className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground">No drivers added yet</p>
                  <Button className="mt-4" onClick={() => setShowAddDriver(true)}>
                    <UserPlus className="h-4 w-4 mr-2" />
                    Add Your First Driver
                  </Button>
                </div>
              ) : (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Name</TableHead>
                      <TableHead>Email</TableHead>
                      <TableHead>License</TableHead>
                      <TableHead>Fuel Limit</TableHead>
                      <TableHead>Usage</TableHead>
                      <TableHead>Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {drivers?.map((driver: any) => (
                      <TableRow key={driver.id}>
                        <TableCell>
                          {driver.firstName} {driver.lastName}
                        </TableCell>
                        <TableCell>{driver.email}</TableCell>
                        <TableCell>{driver.licenseNumber}</TableCell>
                        <TableCell>{driver.fuelLimit}L/month</TableCell>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <div className="text-sm">
                              {driver.currentUsage || 0}L
                            </div>
                            <Badge variant={
                              (driver.currentUsage || 0) > (driver.fuelLimit * 0.8) 
                                ? "destructive" 
                                : "secondary"
                            }>
                              {Math.round(((driver.currentUsage || 0) / driver.fuelLimit) * 100)}%
                            </Badge>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Button size="sm" variant="outline">
                            <Settings className="h-4 w-4" />
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Vehicles Tab */}
        <TabsContent value="vehicles" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Fleet Vehicles</CardTitle>
              <CardDescription>Manage your corporate fleet vehicles</CardDescription>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground mb-4">
                Vehicle management is available through the main Vehicles page.
              </p>
              <Button asChild>
                <a href="/vehicles">Manage Vehicles</a>
              </Button>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Fuel Usage Tab */}
        <TabsContent value="fuel" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Fuel Usage Analytics</CardTitle>
              <CardDescription>Monitor fuel consumption across your fleet</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="text-center py-8">
                <BarChart3 className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <p className="text-muted-foreground">
                  Detailed fuel analytics will be available soon
                </p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Reports Tab */}
        <TabsContent value="reports" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Fleet Reports</CardTitle>
              <CardDescription>Generate comprehensive fleet management reports</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="text-center py-8">
                <BarChart3 className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                <p className="text-muted-foreground">
                  Fleet reporting system will be available soon
                </p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}