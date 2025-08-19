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
import { Car, Plus, CheckCircle, XCircle } from "lucide-react";
import { useAuth } from "@/hooks/useAuth";
import { useToast } from "@/hooks/use-toast";
import { queryClient, apiRequest } from "@/lib/queryClient";

const vehicleSchema = z.object({
  registrationNumber: z.string().min(1, "Registration number is required"),
  vehicleType: z.string().min(1, "Vehicle type is required"),
  make: z.string().optional(),
  model: z.string().optional(),
  year: z.number().min(1900).max(new Date().getFullYear() + 1).optional(),
  fuelType: z.string().min(1, "Fuel type is required"),
  companyId: z.string().optional(),
});

type VehicleFormData = z.infer<typeof vehicleSchema>;

export default function Vehicles() {
  const { user } = useAuth();
  const { toast } = useToast();
  const [activeTab, setActiveTab] = useState("my-vehicles");

  const { data: myVehicles, isLoading: myVehiclesLoading } = useQuery({
    queryKey: ["/api/vehicles/my"],
    enabled: !!user,
  });

  const { data: pendingVehicles, isLoading: pendingLoading } = useQuery({
    queryKey: ["/api/vehicles/pending"],
    enabled: user?.userType === 'admin',
  });

  const form = useForm<VehicleFormData>({
    resolver: zodResolver(vehicleSchema),
    defaultValues: {
      registrationNumber: "",
      vehicleType: "",
      make: "",
      model: "",
      year: new Date().getFullYear(),
      fuelType: "",
    },
  });

  const createVehicleMutation = useMutation({
    mutationFn: (data: VehicleFormData) => apiRequest("/api/vehicles", "POST", data),
    onSuccess: () => {
      toast({
        title: "Vehicle registered successfully",
        description: "Your vehicle is pending approval.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/vehicles/my"] });
      form.reset();
    },
    onError: (error: any) => {
      toast({
        title: "Failed to register vehicle",
        description: error.response?.data?.error || "Please try again.",
        variant: "destructive",
      });
    },
  });

  const approveVehicleMutation = useMutation({
    mutationFn: (vehicleId: string) => apiRequest(`/api/vehicles/${vehicleId}/approve`, "PATCH"),
    onSuccess: () => {
      toast({
        title: "Vehicle approved",
        description: "The vehicle has been approved successfully.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/vehicles/pending"] });
    },
    onError: () => {
      toast({
        title: "Failed to approve vehicle",
        description: "Please try again.",
        variant: "destructive",
      });
    },
  });

  const rejectVehicleMutation = useMutation({
    mutationFn: ({ vehicleId, reason }: { vehicleId: string; reason: string }) =>
      apiRequest(`/api/vehicles/${vehicleId}/reject`, "PATCH", { reason }),
    onSuccess: () => {
      toast({
        title: "Vehicle rejected",
        description: "The vehicle has been rejected.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/vehicles/pending"] });
    },
    onError: () => {
      toast({
        title: "Failed to reject vehicle",
        description: "Please try again.",
        variant: "destructive",
      });
    },
  });

  const onSubmit = (data: VehicleFormData) => {
    createVehicleMutation.mutate(data);
  };

  if (!user) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <h1 className="text-3xl font-bold mb-4">Vehicle Management</h1>
          <p className="text-muted-foreground">Please sign in to manage your vehicles</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold">Vehicle Management</h1>
          <p className="text-muted-foreground">Register and manage your vehicles</p>
        </div>
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
        <TabsList className="grid w-full grid-cols-3">
          <TabsTrigger value="my-vehicles">My Vehicles</TabsTrigger>
          <TabsTrigger value="register">Register Vehicle</TabsTrigger>
          {user.userType === 'admin' && (
            <TabsTrigger value="pending">Pending Approval</TabsTrigger>
          )}
        </TabsList>

        <TabsContent value="my-vehicles">
          <Card>
            <CardHeader>
              <CardTitle>My Vehicles</CardTitle>
              <CardDescription>View and manage your registered vehicles</CardDescription>
            </CardHeader>
            <CardContent>
              {myVehiclesLoading ? (
                <div>Loading vehicles...</div>
              ) : myVehicles?.length === 0 ? (
                <div className="text-center py-8">
                  <Car className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p className="text-muted-foreground mb-4">No vehicles registered yet</p>
                  <Button onClick={() => setActiveTab("register")}>
                    <Plus className="h-4 w-4 mr-2" />
                    Register Your First Vehicle
                  </Button>
                </div>
              ) : (
                <div className="grid gap-4">
                  {myVehicles?.map((vehicle: any) => (
                    <div key={vehicle.id} className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="flex items-center space-x-4">
                        <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                          <Car className="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                          <p className="font-medium">{vehicle.registrationNumber}</p>
                          <p className="text-sm text-muted-foreground">
                            {vehicle.make} {vehicle.model} ({vehicle.year})
                          </p>
                          <div className="flex items-center gap-2 mt-1">
                            <Badge variant="outline">{vehicle.vehicleType}</Badge>
                            <Badge variant="outline">{vehicle.fuelType}</Badge>
                          </div>
                        </div>
                      </div>
                      <div className="text-right">
                        <Badge variant={
                          vehicle.status === 'approved' ? 'default' :
                          vehicle.status === 'pending' ? 'secondary' : 'destructive'
                        }>
                          {vehicle.status}
                        </Badge>
                        {vehicle.status === 'approved' && vehicle.approvedAt && (
                          <p className="text-xs text-muted-foreground mt-1">
                            Approved: {new Date(vehicle.approvedAt).toLocaleDateString()}
                          </p>
                        )}
                        {vehicle.status === 'rejected' && vehicle.rejectionReason && (
                          <p className="text-xs text-red-600 mt-1">
                            Reason: {vehicle.rejectionReason}
                          </p>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="register">
          <Card>
            <CardHeader>
              <CardTitle>Register New Vehicle</CardTitle>
              <CardDescription>Add a new vehicle to your account</CardDescription>
            </CardHeader>
            <CardContent>
              <Form {...form}>
                <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                  <div className="grid gap-4 md:grid-cols-2">
                    <FormField
                      control={form.control}
                      name="registrationNumber"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Registration Number</FormLabel>
                          <FormControl>
                            <Input placeholder="ABC-1234" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <FormField
                      control={form.control}
                      name="vehicleType"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Vehicle Type</FormLabel>
                          <Select onValueChange={field.onChange} defaultValue={field.value}>
                            <FormControl>
                              <SelectTrigger>
                                <SelectValue placeholder="Select vehicle type" />
                              </SelectTrigger>
                            </FormControl>
                            <SelectContent>
                              <SelectItem value="sedan">Sedan</SelectItem>
                              <SelectItem value="suv">SUV</SelectItem>
                              <SelectItem value="truck">Truck</SelectItem>
                              <SelectItem value="van">Van</SelectItem>
                              <SelectItem value="bus">Bus</SelectItem>
                              <SelectItem value="motorcycle">Motorcycle</SelectItem>
                            </SelectContent>
                          </Select>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <FormField
                      control={form.control}
                      name="make"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Make (Optional)</FormLabel>
                          <FormControl>
                            <Input placeholder="Toyota" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <FormField
                      control={form.control}
                      name="model"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Model (Optional)</FormLabel>
                          <FormControl>
                            <Input placeholder="Camry" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <FormField
                      control={form.control}
                      name="year"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Year (Optional)</FormLabel>
                          <FormControl>
                            <Input 
                              type="number" 
                              min="1900" 
                              max={new Date().getFullYear() + 1}
                              {...field}
                              onChange={(e) => field.onChange(parseInt(e.target.value) || undefined)}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <FormField
                      control={form.control}
                      name="fuelType"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Fuel Type</FormLabel>
                          <Select onValueChange={field.onChange} defaultValue={field.value}>
                            <FormControl>
                              <SelectTrigger>
                                <SelectValue placeholder="Select fuel type" />
                              </SelectTrigger>
                            </FormControl>
                            <SelectContent>
                              <SelectItem value="petrol">Petrol</SelectItem>
                              <SelectItem value="diesel">Diesel</SelectItem>
                              <SelectItem value="hybrid">Hybrid</SelectItem>
                            </SelectContent>
                          </Select>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>

                  <Button 
                    type="submit" 
                    disabled={createVehicleMutation.isPending}
                    className="w-full"
                  >
                    {createVehicleMutation.isPending ? "Registering..." : "Register Vehicle"}
                  </Button>
                </form>
              </Form>
            </CardContent>
          </Card>
        </TabsContent>

        {user.userType === 'admin' && (
          <TabsContent value="pending">
            <Card>
              <CardHeader>
                <CardTitle>Pending Vehicle Approvals</CardTitle>
                <CardDescription>Review and approve vehicle registrations</CardDescription>
              </CardHeader>
              <CardContent>
                {pendingLoading ? (
                  <div>Loading pending vehicles...</div>
                ) : pendingVehicles?.length === 0 ? (
                  <div className="text-center py-8">
                    <CheckCircle className="h-12 w-12 text-green-500 mx-auto mb-4" />
                    <p className="text-muted-foreground">No pending vehicle approvals</p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {pendingVehicles?.map((vehicle: any) => (
                      <div key={vehicle.id} className="flex items-center justify-between p-4 border rounded-lg">
                        <div className="flex items-center space-x-4">
                          <div className="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                            <Car className="h-6 w-6 text-orange-600" />
                          </div>
                          <div>
                            <p className="font-medium">{vehicle.registrationNumber}</p>
                            <p className="text-sm text-muted-foreground">
                              {vehicle.make} {vehicle.model} ({vehicle.year})
                            </p>
                            <div className="flex items-center gap-2 mt-1">
                              <Badge variant="outline">{vehicle.vehicleType}</Badge>
                              <Badge variant="outline">{vehicle.fuelType}</Badge>
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                              Registered: {new Date(vehicle.createdAt).toLocaleDateString()}
                            </p>
                          </div>
                        </div>
                        <div className="flex gap-2">
                          <Button
                            size="sm"
                            onClick={() => approveVehicleMutation.mutate(vehicle.id)}
                            disabled={approveVehicleMutation.isPending}
                          >
                            <CheckCircle className="h-4 w-4 mr-1" />
                            Approve
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => {
                              const reason = prompt("Enter rejection reason:");
                              if (reason) {
                                rejectVehicleMutation.mutate({ vehicleId: vehicle.id, reason });
                              }
                            }}
                            disabled={rejectVehicleMutation.isPending}
                          >
                            <XCircle className="h-4 w-4 mr-1" />
                            Reject
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        )}
      </Tabs>
    </div>
  );
}