import { useState } from "react";
import { useQuery, useMutation } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useLocation } from "wouter";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage, FormDescription } from "@/components/ui/form";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { 
  CreditCard, 
  Fuel, 
  ArrowLeftRight, 
  Plus, 
  History,
  Car,
  Building2,
  Receipt,
  DollarSign,
  Clock,
  CheckCircle,
  XCircle,
  AlertCircle
} from "lucide-react";
import { useAuth } from "@/hooks/useAuth";
import { useToast } from "@/hooks/use-toast";
import { queryClient, apiRequest } from "@/lib/queryClient";

// Transaction Management - Create and manage fuel transactions
const fuelPurchaseSchema = z.object({
  vehicleId: z.string().min(1, "Vehicle is required"),
  fuelType: z.enum(["petrol", "diesel"], { required_error: "Fuel type is required" }),
  quantity: z.string().min(1, "Quantity is required").refine(val => !isNaN(Number(val)) && Number(val) > 0, "Must be a positive number"),
  pricePerLiter: z.string().min(1, "Price per liter is required").refine(val => !isNaN(Number(val)) && Number(val) > 0, "Must be a positive number"),
  merchantId: z.string().optional(),
  description: z.string().optional(),
});

const transferSchema = z.object({
  recipientEmail: z.string().email("Valid email is required"),
  fuelType: z.enum(["petrol", "diesel"], { required_error: "Fuel type is required" }),
  quantity: z.string().min(1, "Quantity is required").refine(val => !isNaN(Number(val)) && Number(val) > 0, "Must be a positive number"),
  description: z.string().optional(),
});

const topUpSchema = z.object({
  fuelType: z.enum(["petrol", "diesel"], { required_error: "Fuel type is required" }),
  amount: z.string().min(1, "Amount is required").refine(val => !isNaN(Number(val)) && Number(val) > 0, "Must be a positive number"),
  paymentMethod: z.enum(["card", "bank_transfer", "mobile_money"], { required_error: "Payment method is required" }),
});

type FuelPurchaseData = z.infer<typeof fuelPurchaseSchema>;
type TransferData = z.infer<typeof transferSchema>;
type TopUpData = z.infer<typeof topUpSchema>;

export default function Transactions() {
  const { user } = useAuth();
  const { toast } = useToast();
  const [location, setLocation] = useLocation();
  const [activeTab, setActiveTab] = useState("purchase");

  // Get the action from URL path
  const isNewTransaction = location.includes("/new");

  // Queries
  const { data: userVehicles, isLoading: vehiclesLoading } = useQuery({
    queryKey: ["/api/vehicles/user"],
    enabled: !!user,
  });

  const { data: merchants, isLoading: merchantsLoading } = useQuery({
    queryKey: ["/api/merchants/active"],
    enabled: !!user,
  });

  const { data: fuelBalances, isLoading: balancesLoading } = useQuery({
    queryKey: ["/api/fuel-balances"],
    enabled: !!user,
  });

  const { data: recentTransactions, isLoading: transactionsLoading } = useQuery({
    queryKey: ["/api/transactions/recent"],
    enabled: !!user && !isNewTransaction,
  });

  const { data: transactionLimits, isLoading: limitsLoading } = useQuery({
    queryKey: ["/api/transaction-limits"],
    enabled: !!user,
  });

  // Forms
  const purchaseForm = useForm<FuelPurchaseData>({
    resolver: zodResolver(fuelPurchaseSchema),
    defaultValues: {
      vehicleId: "",
      fuelType: "petrol",
      quantity: "",
      pricePerLiter: "1.50",
      merchantId: "",
      description: "",
    },
  });

  const transferForm = useForm<TransferData>({
    resolver: zodResolver(transferSchema),
    defaultValues: {
      recipientEmail: "",
      fuelType: "petrol",
      quantity: "",
      description: "",
    },
  });

  const topUpForm = useForm<TopUpData>({
    resolver: zodResolver(topUpSchema),
    defaultValues: {
      fuelType: "petrol",
      amount: "",
      paymentMethod: "card",
    },
  });

  // Mutations
  const fuelPurchaseMutation = useMutation({
    mutationFn: (data: FuelPurchaseData) => {
      const totalAmount = Number(data.quantity) * Number(data.pricePerLiter);
      return apiRequest("/api/transactions/fuel-purchase", "POST", {
        ...data,
        quantity: Number(data.quantity),
        pricePerLiter: Number(data.pricePerLiter),
        totalAmount,
      });
    },
    onSuccess: () => {
      toast({
        title: "Fuel purchase successful",
        description: "Your fuel purchase has been processed successfully.",
      });
      purchaseForm.reset();
      queryClient.invalidateQueries({ queryKey: ["/api/fuel-balances"] });
      queryClient.invalidateQueries({ queryKey: ["/api/transactions/recent"] });
      setLocation("/transactions");
    },
    onError: (error: any) => {
      toast({
        title: "Purchase failed",
        description: error.message || "Failed to process fuel purchase",
        variant: "destructive",
      });
    },
  });

  const transferMutation = useMutation({
    mutationFn: (data: TransferData) => apiRequest("/api/transactions/transfer", "POST", {
      ...data,
      quantity: Number(data.quantity),
    }),
    onSuccess: () => {
      toast({
        title: "Transfer successful",
        description: "Fuel has been transferred successfully.",
      });
      transferForm.reset();
      queryClient.invalidateQueries({ queryKey: ["/api/fuel-balances"] });
      queryClient.invalidateQueries({ queryKey: ["/api/transactions/recent"] });
    },
    onError: (error: any) => {
      toast({
        title: "Transfer failed",
        description: error.message || "Failed to transfer fuel",
        variant: "destructive",
      });
    },
  });

  const topUpMutation = useMutation({
    mutationFn: (data: TopUpData) => apiRequest("/api/transactions/top-up", "POST", {
      ...data,
      amount: Number(data.amount),
    }),
    onSuccess: () => {
      toast({
        title: "Top-up successful",
        description: "Your account has been topped up successfully.",
      });
      topUpForm.reset();
      queryClient.invalidateQueries({ queryKey: ["/api/fuel-balances"] });
      queryClient.invalidateQueries({ queryKey: ["/api/transactions/recent"] });
    },
    onError: (error: any) => {
      toast({
        title: "Top-up failed",
        description: error.message || "Failed to process top-up",
        variant: "destructive",
      });
    },
  });

  if (!user) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <h1 className="text-3xl font-bold mb-4">Transactions</h1>
          <p className="text-muted-foreground">Please sign in to manage transactions</p>
        </div>
      </div>
    );
  }

  // Calculate total amount for purchase
  const calculatePurchaseTotal = () => {
    const quantity = Number(purchaseForm.watch("quantity")) || 0;
    const price = Number(purchaseForm.watch("pricePerLiter")) || 0;
    return quantity * price;
  };

  if (isNewTransaction) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold">New Transaction</h1>
            <p className="text-muted-foreground">Create a new fuel transaction</p>
          </div>
          <Button variant="outline" onClick={() => setLocation("/transactions")}>
            <ArrowLeftRight className="h-4 w-4 mr-2" />
            Back to Transactions
          </Button>
        </div>

        {/* Current Fuel Balances */}
        <Card className="mb-6">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Fuel className="h-5 w-5" />
              Current Fuel Balances
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
                    <p className="text-sm text-muted-foreground">Available balance</p>
                  </div>
                  <div className="text-right">
                    <p className="text-2xl font-bold">
                      {Array.isArray(fuelBalances) ? fuelBalances.find((b: any) => b.fuelType === 'petrol')?.balance || '0.00' : '0.00'}L
                    </p>
                  </div>
                </div>
                <div className="flex items-center justify-between p-4 border rounded-lg">
                  <div>
                    <p className="font-medium">Diesel</p>
                    <p className="text-sm text-muted-foreground">Available balance</p>
                  </div>
                  <div className="text-right">
                    <p className="text-2xl font-bold">
                      {Array.isArray(fuelBalances) ? fuelBalances.find((b: any) => b.fuelType === 'diesel')?.balance || '0.00' : '0.00'}L
                    </p>
                  </div>
                </div>
              </div>
            )}
          </CardContent>
        </Card>

        <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
          <TabsList className="grid w-full grid-cols-3">
            <TabsTrigger value="purchase">Fuel Purchase</TabsTrigger>
            <TabsTrigger value="transfer">Transfer Fuel</TabsTrigger>
            <TabsTrigger value="topup">Top Up Balance</TabsTrigger>
          </TabsList>

          {/* Fuel Purchase Tab */}
          <TabsContent value="purchase">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Car className="h-5 w-5" />
                  Fuel Purchase
                </CardTitle>
                <CardDescription>
                  Purchase fuel for your vehicle from a registered merchant
                </CardDescription>
              </CardHeader>
              <CardContent>
                <Form {...purchaseForm}>
                  <form onSubmit={purchaseForm.handleSubmit((data) => fuelPurchaseMutation.mutate(data))} className="space-y-6">
                    <FormField
                      control={purchaseForm.control}
                      name="vehicleId"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Vehicle</FormLabel>
                          <Select onValueChange={field.onChange} defaultValue={field.value}>
                            <FormControl>
                              <SelectTrigger>
                                <SelectValue placeholder="Select a vehicle" />
                              </SelectTrigger>
                            </FormControl>
                            <SelectContent>
                              {vehiclesLoading ? (
                                <div className="p-2">Loading vehicles...</div>
                              ) : userVehicles?.length === 0 ? (
                                <div className="p-2 text-muted-foreground">No vehicles registered</div>
                              ) : (
                                userVehicles?.map((vehicle: any) => (
                                  <SelectItem key={vehicle.id} value={vehicle.id}>
                                    {vehicle.registrationNumber} - {vehicle.make} {vehicle.model}
                                  </SelectItem>
                                ))
                              )}
                            </SelectContent>
                          </Select>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <div className="grid gap-4 md:grid-cols-2">
                      <FormField
                        control={purchaseForm.control}
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
                        control={purchaseForm.control}
                        name="merchantId"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Fuel Station (Optional)</FormLabel>
                            <Select onValueChange={field.onChange} defaultValue={field.value}>
                              <FormControl>
                                <SelectTrigger>
                                  <SelectValue placeholder="Select fuel station" />
                                </SelectTrigger>
                              </FormControl>
                              <SelectContent>
                                {merchantsLoading ? (
                                  <div className="p-2">Loading merchants...</div>
                                ) : (
                                  merchants?.map((merchant: any) => (
                                    <SelectItem key={merchant.id} value={merchant.id}>
                                      {merchant.stationName}
                                    </SelectItem>
                                  ))
                                )}
                              </SelectContent>
                            </Select>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                      <FormField
                        control={purchaseForm.control}
                        name="quantity"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Quantity (Liters)</FormLabel>
                            <FormControl>
                              <Input 
                                type="number" 
                                step="0.01" 
                                placeholder="25.00" 
                                {...field} 
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />

                      <FormField
                        control={purchaseForm.control}
                        name="pricePerLiter"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Price per Liter ($)</FormLabel>
                            <FormControl>
                              <Input 
                                type="number" 
                                step="0.01" 
                                placeholder="1.50" 
                                {...field} 
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                    </div>

                    <FormField
                      control={purchaseForm.control}
                      name="description"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Description (Optional)</FormLabel>
                          <FormControl>
                            <Input placeholder="Additional notes..." {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    {/* Total Amount Display */}
                    <div className="p-4 bg-muted rounded-lg">
                      <div className="flex items-center justify-between">
                        <span className="font-medium">Total Amount:</span>
                        <span className="text-2xl font-bold">${calculatePurchaseTotal().toFixed(2)}</span>
                      </div>
                    </div>

                    <Button 
                      type="submit" 
                      className="w-full"
                      disabled={fuelPurchaseMutation.isPending}
                    >
                      {fuelPurchaseMutation.isPending ? "Processing..." : "Complete Purchase"}
                    </Button>
                  </form>
                </Form>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Transfer Fuel Tab */}
          <TabsContent value="transfer">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <ArrowLeftRight className="h-5 w-5" />
                  Transfer Fuel
                </CardTitle>
                <CardDescription>
                  Transfer fuel from your balance to another user
                </CardDescription>
              </CardHeader>
              <CardContent>
                <Form {...transferForm}>
                  <form onSubmit={transferForm.handleSubmit((data) => transferMutation.mutate(data))} className="space-y-6">
                    <FormField
                      control={transferForm.control}
                      name="recipientEmail"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Recipient Email</FormLabel>
                          <FormControl>
                            <Input 
                              type="email" 
                              placeholder="recipient@example.com" 
                              {...field} 
                            />
                          </FormControl>
                          <FormDescription>
                            Enter the email address of the person you want to transfer fuel to
                          </FormDescription>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <div className="grid gap-4 md:grid-cols-2">
                      <FormField
                        control={transferForm.control}
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
                        control={transferForm.control}
                        name="quantity"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Quantity (Liters)</FormLabel>
                            <FormControl>
                              <Input 
                                type="number" 
                                step="0.01" 
                                placeholder="10.00" 
                                {...field} 
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                    </div>

                    <FormField
                      control={transferForm.control}
                      name="description"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Description (Optional)</FormLabel>
                          <FormControl>
                            <Input placeholder="Transfer reason..." {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <Button 
                      type="submit" 
                      className="w-full"
                      disabled={transferMutation.isPending}
                    >
                      {transferMutation.isPending ? "Transferring..." : "Transfer Fuel"}
                    </Button>
                  </form>
                </Form>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Top Up Tab */}
          <TabsContent value="topup">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <DollarSign className="h-5 w-5" />
                  Top Up Balance
                </CardTitle>
                <CardDescription>
                  Add funds to your fuel balance
                </CardDescription>
              </CardHeader>
              <CardContent>
                <Form {...topUpForm}>
                  <form onSubmit={topUpForm.handleSubmit((data) => topUpMutation.mutate(data))} className="space-y-6">
                    <div className="grid gap-4 md:grid-cols-2">
                      <FormField
                        control={topUpForm.control}
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
                        control={topUpForm.control}
                        name="amount"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Amount ($)</FormLabel>
                            <FormControl>
                              <Input 
                                type="number" 
                                step="0.01" 
                                placeholder="50.00" 
                                {...field} 
                              />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                    </div>

                    <FormField
                      control={topUpForm.control}
                      name="paymentMethod"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Payment Method</FormLabel>
                          <Select onValueChange={field.onChange} defaultValue={field.value}>
                            <FormControl>
                              <SelectTrigger>
                                <SelectValue placeholder="Select payment method" />
                              </SelectTrigger>
                            </FormControl>
                            <SelectContent>
                              <SelectItem value="card">Credit/Debit Card</SelectItem>
                              <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
                              <SelectItem value="mobile_money">Mobile Money</SelectItem>
                            </SelectContent>
                          </Select>
                          <FormMessage />
                        </FormItem>
                      )}
                    />

                    <Button 
                      type="submit" 
                      className="w-full"
                      disabled={topUpMutation.isPending}
                    >
                      {topUpMutation.isPending ? "Processing..." : "Top Up Balance"}
                    </Button>
                  </form>
                </Form>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    );
  }

  // Main transactions page
  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold">Transactions</h1>
          <p className="text-muted-foreground">Manage your fuel transactions and transfers</p>
        </div>
        <Button onClick={() => setLocation("/transactions/new")}>
          <Plus className="h-4 w-4 mr-2" />
          New Transaction
        </Button>
      </div>

      {/* Transaction Limits Info */}
      {!limitsLoading && transactionLimits && (
        <Card className="mb-6">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <AlertCircle className="h-5 w-5" />
              Transaction Limits
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-3">
              <div>
                <p className="text-sm font-medium">Daily Purchase Limit</p>
                <p className="text-2xl font-bold">${transactionLimits.dailyPurchaseLimit}</p>
              </div>
              <div>
                <p className="text-sm font-medium">Monthly Limit</p>
                <p className="text-2xl font-bold">${transactionLimits.monthlyPurchaseLimit}</p>
              </div>
              <div>
                <p className="text-sm font-medium">Transfer Limit</p>
                <p className="text-2xl font-bold">${transactionLimits.dailyTransferLimit}/day</p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Recent Transactions */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <History className="h-5 w-5" />
            Recent Transactions
          </CardTitle>
          <CardDescription>Your recent fuel transactions and transfers</CardDescription>
        </CardHeader>
        <CardContent>
          {transactionsLoading ? (
            <div>Loading transactions...</div>
          ) : recentTransactions?.length === 0 ? (
            <div className="text-center py-8">
              <Receipt className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
              <p className="text-muted-foreground">No transactions yet</p>
              <Button className="mt-4" onClick={() => setLocation("/transactions/new")}>
                <Plus className="h-4 w-4 mr-2" />
                Create Your First Transaction
              </Button>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Amount</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Description</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {recentTransactions?.map((transaction: any) => (
                  <TableRow key={transaction.id}>
                    <TableCell>
                      {new Date(transaction.createdAt).toLocaleDateString()}
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline">
                        {transaction.type}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      {transaction.quantity ? `${transaction.quantity}L` : `$${transaction.amount}`}
                    </TableCell>
                    <TableCell>
                      <Badge variant={
                        transaction.status === 'completed' ? 'default' :
                        transaction.status === 'pending' ? 'secondary' : 'destructive'
                      }>
                        {transaction.status}
                      </Badge>
                    </TableCell>
                    <TableCell className="max-w-xs truncate">
                      {transaction.description || '-'}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>
    </div>
  );
}