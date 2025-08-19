import type { Express } from "express";
import { createServer, type Server } from "http";
import { storage } from "./storage";
import { 
  insertUserSchema, 
  insertVehicleSchema, 
  insertTransactionSchema, 
  insertCouponSchema, 
  insertMerchantSchema 
} from "@shared/schema";
import { z } from "zod";

// Extend Express Request type
declare global {
  namespace Express {
    interface User {
      id: string;
      email?: string;
      firstName?: string;
      lastName?: string;
      userType?: string;
    }
  }
}

export async function registerRoutes(app: Express): Promise<Server> {
  // Authentication routes
  app.get("/api/auth/user", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }
      
      let user = await storage.getUser(userId);
      if (!user) {
        // In development, create the mock user if it doesn't exist
        if (process.env.NODE_ENV === "development") {
          user = await storage.upsertUser({
            id: userId,
            email: req.user.email || "dev@example.com",
            firstName: req.user.firstName || "Dev",
            lastName: req.user.lastName || "User",
            userType: req.user.userType || "admin"
          });
        } else {
          return res.status(404).json({ error: "User not found" });
        }
      }
      
      res.json(user);
    } catch (error) {
      res.status(500).json({ error: "Failed to get user" });
    }
  });

  app.get("/api/auth/status", async (req, res) => {
    try {
      const isAuthenticated = !!req.user?.id;
      res.json({ 
        authenticated: isAuthenticated,
        user: isAuthenticated ? req.user : null,
        development: process.env.NODE_ENV === "development"
      });
    } catch (error) {
      res.status(500).json({ error: "Failed to get auth status" });
    }
  });

  // Dashboard stats
  app.get("/api/dashboard/stats", async (req, res) => {
    try {
      const stats = await storage.getSystemStats();
      res.json(stats);
    } catch (error) {
      res.status(500).json({ error: "Failed to get stats" });
    }
  });

  // Fuel balance routes
  app.get("/api/fuel-balances", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }
      
      await storage.initializeFuelBalances(userId);
      const petrolBalance = await storage.getFuelBalance(userId, 'petrol');
      const dieselBalance = await storage.getFuelBalance(userId, 'diesel');
      
      res.json([
        {
          fuelType: 'petrol',
          balance: petrolBalance?.balance || '0.00'
        },
        {
          fuelType: 'diesel',
          balance: dieselBalance?.balance || '0.00'
        }
      ]);
    } catch (error) {
      res.status(500).json({ error: "Failed to get fuel balances" });
    }
  });

  // Transaction routes
  app.get("/api/transactions/my", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }
      
      const transactions = await storage.getTransactionsByUser(userId, 20);
      res.json(transactions);
    } catch (error) {
      res.status(500).json({ error: "Failed to get transactions" });
    }
  });

  // Vehicle routes
  app.post("/api/vehicles", async (req, res) => {
    try {
      const vehicleData = insertVehicleSchema.parse(req.body);
      const vehicle = await storage.createVehicle({
        ...vehicleData,
        ownerId: req.user?.id || vehicleData.ownerId
      });
      res.status(201).json(vehicle);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ error: "Invalid vehicle data", details: error.errors });
      }
      res.status(500).json({ error: "Failed to create vehicle" });
    }
  });

  app.get("/api/vehicles/my", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }
      
      const vehicles = await storage.getVehiclesByOwner(userId);
      res.json(vehicles);
    } catch (error) {
      res.status(500).json({ error: "Failed to get vehicles" });
    }
  });

  app.get("/api/vehicles/pending", async (req, res) => {
    try {
      const vehicles = await storage.getPendingVehicles();
      res.json(vehicles);
    } catch (error) {
      res.status(500).json({ error: "Failed to get pending vehicles" });
    }
  });

  app.patch("/api/vehicles/:id/approve", async (req, res) => {
    try {
      const { id } = req.params;
      const adminId = req.user?.id;
      
      if (!adminId) {
        return res.status(401).json({ error: "Not authenticated" });
      }

      const vehicle = await storage.approveVehicle(id, adminId);
      res.json(vehicle);
    } catch (error) {
      res.status(500).json({ error: "Failed to approve vehicle" });
    }
  });

  app.patch("/api/vehicles/:id/reject", async (req, res) => {
    try {
      const { id } = req.params;
      const { reason } = req.body;
      const adminId = req.user?.id;
      
      if (!adminId) {
        return res.status(401).json({ error: "Not authenticated" });
      }

      const vehicle = await storage.rejectVehicle(id, adminId, reason);
      res.json(vehicle);
    } catch (error) {
      res.status(500).json({ error: "Failed to reject vehicle" });
    }
  });

  // Fuel balance routes
  app.get("/api/fuel-balances", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }

      const petrolBalance = await storage.getFuelBalance(userId, 'petrol');
      const dieselBalance = await storage.getFuelBalance(userId, 'diesel');
      
      res.json({
        petrol: petrolBalance?.balance || '0.00',
        diesel: dieselBalance?.balance || '0.00'
      });
    } catch (error) {
      res.status(500).json({ error: "Failed to get fuel balances" });
    }
  });

  // Transaction routes
  app.post("/api/transactions", async (req, res) => {
    try {
      const transactionData = insertTransactionSchema.parse(req.body);
      const transaction = await storage.createTransaction({
        ...transactionData,
        userId: req.user?.id || transactionData.userId
      });
      res.status(201).json(transaction);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ error: "Invalid transaction data", details: error.errors });
      }
      res.status(500).json({ error: "Failed to create transaction" });
    }
  });

  app.get("/api/transactions/my", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }
      
      const limit = parseInt(req.query.limit as string) || 50;
      const transactions = await storage.getTransactionsByUser(userId, limit);
      res.json(transactions);
    } catch (error) {
      res.status(500).json({ error: "Failed to get transactions" });
    }
  });

  // Coupon routes
  app.post("/api/coupons", async (req, res) => {
    try {
      if (!req.user?.id) {
        return res.status(401).json({ error: "Not authenticated" });
      }
      
      // Only admins can create coupons
      if (req.user.userType !== 'admin') {
        return res.status(403).json({ error: "Only administrators can create coupons" });
      }
      
      const couponData = insertCouponSchema.parse(req.body);
      const coupon = await storage.createCoupon({
        ...couponData,
        createdBy: req.user.id
      });
      res.status(201).json(coupon);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ error: "Invalid coupon data", details: error.errors });
      }
      res.status(500).json({ error: "Failed to create coupon" });
    }
  });

  app.get("/api/coupons/active", async (req, res) => {
    try {
      const limit = parseInt(req.query.limit as string) || 100;
      const coupons = await storage.getActiveCoupons(limit);
      res.json(coupons);
    } catch (error) {
      res.status(500).json({ error: "Failed to get active coupons" });
    }
  });

  app.post("/api/coupons/redeem", async (req, res) => {
    try {
      const { code } = req.body;
      const userId = req.user?.id;
      
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }

      const coupon = await storage.redeemCoupon(code, userId);
      
      // Update user's fuel balance
      await storage.updateFuelBalance(userId, coupon.fuelType, parseFloat(coupon.amount));
      
      res.json(coupon);
    } catch (error) {
      res.status(400).json({ error: error instanceof Error ? error.message : "Failed to redeem coupon" });
    }
  });

  // Merchant routes
  app.get("/api/merchants", async (req, res) => {
    try {
      const merchants = await storage.getMerchantsList();
      res.json(merchants);
    } catch (error) {
      res.status(500).json({ error: "Failed to get merchants" });
    }
  });

  app.post("/api/merchants", async (req, res) => {
    try {
      const merchantData = insertMerchantSchema.parse(req.body);
      const merchant = await storage.createMerchant({
        ...merchantData,
        userId: req.user?.id || merchantData.userId
      });
      res.status(201).json(merchant);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ error: "Invalid merchant data", details: error.errors });
      }
      res.status(500).json({ error: "Failed to create merchant" });
    }
  });

  // Company routes
  app.get("/api/companies", async (req, res) => {
    try {
      const companies = await storage.getCompaniesList();
      res.json(companies);
    } catch (error) {
      res.status(500).json({ error: "Failed to get companies" });
    }
  });

  app.post("/api/companies", async (req, res) => {
    try {
      const { name, registrationNumber, address, contactEmail, contactPhone } = req.body;
      const company = await storage.createCompany({
        name,
        registrationNumber,
        address,
        contactEmail,
        contactPhone
      });
      res.status(201).json(company);
    } catch (error) {
      res.status(500).json({ error: "Failed to create company" });
    }
  });

  // Transaction routes
  app.get("/api/transactions/recent", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }
      
      const transactions = await storage.getUserTransactions(userId);
      res.json(transactions);
    } catch (error) {
      res.status(500).json({ error: "Failed to get transactions" });
    }
  });

  app.post("/api/transactions/fuel-purchase", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }

      const transactionData = insertTransactionSchema.parse({
        ...req.body,
        userId,
        type: "fuel_purchase",
        status: "completed"
      });

      const transaction = await storage.createTransaction(transactionData);
      res.json(transaction);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ 
          error: "Invalid transaction data", 
          details: error.errors 
        });
      }
      res.status(500).json({ error: "Failed to process purchase" });
    }
  });

  app.post("/api/transactions/transfer", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }

      const { recipientEmail, fuelType, quantity, description } = req.body;
      
      // Find recipient user
      const recipient = await storage.getUserByEmail(recipientEmail);
      if (!recipient) {
        return res.status(404).json({ error: "Recipient not found" });
      }

      const transactionData = insertTransactionSchema.parse({
        userId,
        type: "transfer_out",
        fuelType,
        quantity,
        description,
        status: "completed"
      });

      const transaction = await storage.createTransaction(transactionData);
      res.json(transaction);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ 
          error: "Invalid transfer data", 
          details: error.errors 
        });
      }
      res.status(500).json({ error: "Failed to process transfer" });
    }
  });

  app.post("/api/transactions/top-up", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }

      const transactionData = insertTransactionSchema.parse({
        ...req.body,
        userId,
        type: "top_up",
        status: "completed"
      });

      const transaction = await storage.createTransaction(transactionData);
      res.json(transaction);
    } catch (error) {
      if (error instanceof z.ZodError) {
        return res.status(400).json({ 
          error: "Invalid top-up data", 
          details: error.errors 
        });
      }
      res.status(500).json({ error: "Failed to process top-up" });
    }
  });

  app.get("/api/transaction-limits", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }
      
      // Return default transaction limits
      const limits = {
        dailyPurchaseLimit: 100,
        monthlyPurchaseLimit: 2500,
        dailyTransferLimit: 50
      };
      
      res.json(limits);
    } catch (error) {
      res.status(500).json({ error: "Failed to get limits" });
    }
  });

  app.get("/api/vehicles/user", async (req, res) => {
    try {
      const userId = req.user?.id;
      if (!userId) {
        return res.status(401).json({ error: "Not authenticated" });
      }
      
      const vehicles = await storage.getUserVehicles(userId);
      res.json(vehicles);
    } catch (error) {
      res.status(500).json({ error: "Failed to get user vehicles" });
    }
  });

  app.get("/api/merchants/active", async (req, res) => {
    try {
      const merchants = await storage.getActiveMerchants();
      res.json(merchants);
    } catch (error) {
      res.status(500).json({ error: "Failed to get merchants" });
    }
  });

  const httpServer = createServer(app);

  return httpServer;
}
