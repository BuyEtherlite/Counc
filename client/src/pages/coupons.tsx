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
import { Textarea } from "@/components/ui/textarea";
import { Ticket, Plus, Gift } from "lucide-react";
import { useAuth } from "@/hooks/useAuth";
import { useToast } from "@/hooks/use-toast";
import { queryClient, apiRequest } from "@/lib/queryClient";

const couponSchema = z.object({
  fuelType: z.string().min(1, "Fuel type is required"),
  amount: z.string().min(1, "Amount is required"),
  description: z.string().optional(),
  expiryDate: z.string().optional(),
});

const redeemSchema = z.object({
  code: z.string().min(1, "Coupon code is required"),
});

type CouponFormData = z.infer<typeof couponSchema>;
type RedeemFormData = z.infer<typeof redeemSchema>;

export default function Coupons() {
  const { user } = useAuth();
  const { toast } = useToast();
  const [activeTab, setActiveTab] = useState("redeem");

  const { data: activeCoupons, isLoading: couponsLoading } = useQuery({
    queryKey: ["/api/coupons/active"],
    enabled: user?.userType === 'admin',
  });

  const createForm = useForm<CouponFormData>({
    resolver: zodResolver(couponSchema),
    defaultValues: {
      fuelType: "",
      amount: "",
      description: "",
      expiryDate: "",
    },
  });

  const redeemForm = useForm<RedeemFormData>({
    resolver: zodResolver(redeemSchema),
    defaultValues: {
      code: "",
    },
  });

  const createCouponMutation = useMutation({
    mutationFn: (data: CouponFormData) => {
      const payload = {
        ...data,
        expiryDate: data.expiryDate ? new Date(data.expiryDate).toISOString() : undefined,
      };
      return apiRequest("/api/coupons", "POST", payload);
    },
    onSuccess: () => {
      toast({
        title: "Coupon created successfully",
        description: "The coupon has been generated and is now active.",
      });
      queryClient.invalidateQueries({ queryKey: ["/api/coupons/active"] });
      createForm.reset();
    },
    onError: (error: any) => {
      toast({
        title: "Failed to create coupon",
        description: error.response?.data?.error || "Please try again.",
        variant: "destructive",
      });
    },
  });

  const redeemCouponMutation = useMutation({
    mutationFn: (data: RedeemFormData) => apiRequest("/api/coupons/redeem", "POST", data),
    onSuccess: (data: any) => {
      toast({
        title: "Coupon redeemed successfully",
        description: `${data.amount}L of ${data.fuelType} has been added to your balance.`,
      });
      queryClient.invalidateQueries({ queryKey: ["/api/fuel-balances"] });
      redeemForm.reset();
    },
    onError: (error: any) => {
      toast({
        title: "Failed to redeem coupon",
        description: error.response?.data?.error || "Please check the coupon code and try again.",
        variant: "destructive",
      });
    },
  });

  const onCreateSubmit = (data: CouponFormData) => {
    createCouponMutation.mutate(data);
  };

  const onRedeemSubmit = (data: RedeemFormData) => {
    redeemCouponMutation.mutate(data);
  };

  if (!user) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <h1 className="text-3xl font-bold mb-4">Fuel Coupons</h1>
          <p className="text-muted-foreground">Please sign in to redeem coupons</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold">Fuel Coupons</h1>
          <p className="text-muted-foreground">Redeem coupons to add fuel to your balance</p>
        </div>
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="redeem">Redeem Coupon</TabsTrigger>
          {user.userType === 'admin' && (
            <TabsTrigger value="manage">Manage Coupons</TabsTrigger>
          )}
        </TabsList>

        <TabsContent value="redeem">
          <div className="grid gap-6 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Gift className="h-5 w-5" />
                  Redeem Coupon
                </CardTitle>
                <CardDescription>
                  Enter your coupon code to add fuel to your balance
                </CardDescription>
              </CardHeader>
              <CardContent>
                <Form {...redeemForm}>
                  <form onSubmit={redeemForm.handleSubmit(onRedeemSubmit)} className="space-y-4">
                    <FormField
                      control={redeemForm.control}
                      name="code"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Coupon Code</FormLabel>
                          <FormControl>
                            <Input 
                              placeholder="FUEL-PET-10L-ABCD12" 
                              {...field}
                              className="font-mono"
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <Button 
                      type="submit" 
                      disabled={redeemCouponMutation.isPending}
                      className="w-full"
                    >
                      {redeemCouponMutation.isPending ? "Redeeming..." : "Redeem Coupon"}
                    </Button>
                  </form>
                </Form>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>How Coupons Work</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-start gap-3">
                  <div className="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center text-sm font-medium text-blue-600 mt-0.5">
                    1
                  </div>
                  <div>
                    <p className="font-medium">Get a Coupon Code</p>
                    <p className="text-sm text-muted-foreground">
                      Obtain a valid coupon code from authorized sources
                    </p>
                  </div>
                </div>
                
                <div className="flex items-start gap-3">
                  <div className="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center text-sm font-medium text-blue-600 mt-0.5">
                    2
                  </div>
                  <div>
                    <p className="font-medium">Enter the Code</p>
                    <p className="text-sm text-muted-foreground">
                      Type the coupon code exactly as provided
                    </p>
                  </div>
                </div>
                
                <div className="flex items-start gap-3">
                  <div className="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center text-sm font-medium text-blue-600 mt-0.5">
                    3
                  </div>
                  <div>
                    <p className="font-medium">Fuel Added</p>
                    <p className="text-sm text-muted-foreground">
                      The fuel amount will be added to your balance instantly
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {user.userType === 'admin' && (
          <TabsContent value="manage">
            <div className="grid gap-6 lg:grid-cols-2">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Plus className="h-5 w-5" />
                    Create New Coupon
                  </CardTitle>
                  <CardDescription>
                    Generate a new fuel coupon with unique code
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <Form {...createForm}>
                    <form onSubmit={createForm.handleSubmit(onCreateSubmit)} className="space-y-4">
                      <FormField
                        control={createForm.control}
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
                              </SelectContent>
                            </Select>
                            <FormMessage />
                          </FormItem>
                        )}
                      />

                      <FormField
                        control={createForm.control}
                        name="amount"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Amount (Liters)</FormLabel>
                            <FormControl>
                              <Input 
                                type="number" 
                                min="0.01" 
                                step="0.01"
                                placeholder="10.00" 
                                {...field} 
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />

                      <FormField
                        control={createForm.control}
                        name="description"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Description (Optional)</FormLabel>
                            <FormControl>
                              <Textarea 
                                placeholder="Promotional coupon for new users..."
                                {...field} 
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />

                      <FormField
                        control={createForm.control}
                        name="expiryDate"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Expiry Date (Optional)</FormLabel>
                            <FormControl>
                              <Input 
                                type="date" 
                                {...field} 
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />

                      <Button 
                        type="submit" 
                        disabled={createCouponMutation.isPending}
                        className="w-full"
                      >
                        {createCouponMutation.isPending ? "Creating..." : "Create Coupon"}
                      </Button>
                    </form>
                  </Form>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Active Coupons</CardTitle>
                  <CardDescription>
                    View and manage existing coupons
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  {couponsLoading ? (
                    <div>Loading coupons...</div>
                  ) : activeCoupons?.length === 0 ? (
                    <div className="text-center py-8">
                      <Ticket className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                      <p className="text-muted-foreground">No active coupons</p>
                    </div>
                  ) : (
                    <div className="space-y-4 max-h-96 overflow-y-auto">
                      {activeCoupons?.map((coupon: any) => (
                        <div key={coupon.id} className="p-4 border rounded-lg">
                          <div className="flex items-center justify-between mb-2">
                            <code className="bg-muted px-2 py-1 rounded text-sm font-mono">
                              {coupon.code}
                            </code>
                            <Badge variant="default">Active</Badge>
                          </div>
                          <div className="text-sm space-y-1">
                            <p>
                              <span className="font-medium">Fuel:</span> {coupon.fuelType}
                            </p>
                            <p>
                              <span className="font-medium">Amount:</span> {coupon.amount}L
                            </p>
                            <p>
                              <span className="font-medium">Created:</span>{" "}
                              {new Date(coupon.createdAt).toLocaleDateString()}
                            </p>
                            {coupon.expiryDate && (
                              <p>
                                <span className="font-medium">Expires:</span>{" "}
                                {new Date(coupon.expiryDate).toLocaleDateString()}
                              </p>
                            )}
                            {coupon.description && (
                              <p className="text-muted-foreground">
                                {coupon.description}
                              </p>
                            )}
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>
          </TabsContent>
        )}
      </Tabs>
    </div>
  );
}