import {
  users,
  companies,
  vehicles,
  fuelBalances,
  transactions,
  coupons,
  merchants,
  merchantEmployees,
  withdrawalRequests,
  adminRoles,
  transactionLimits,
  documents,
  type User,
  type UpsertUser,
  type Company,
  type Vehicle,
  type InsertVehicle,
  type FuelBalance,
  type Transaction,
  type InsertTransaction,
  type Coupon,
  type InsertCoupon,
  type Merchant,
  type InsertMerchant,
  type WithdrawalRequest,
  type AdminRole,
  type TransactionLimit,
} from "@shared/schema";
import { db } from "./db";
import { eq, and, desc, sum, count, gte, lte, sql } from "drizzle-orm";
import { randomUUID } from "crypto";

export interface IStorage {
  // User operations (Required for Replit Auth)
  getUser(id: string): Promise<User | undefined>;
  upsertUser(user: UpsertUser): Promise<User>;
  
  // Company operations
  createCompany(data: { name: string; registrationNumber?: string; address?: string; contactEmail?: string; contactPhone?: string }): Promise<Company>;
  getCompanyById(id: string): Promise<Company | undefined>;
  getCompaniesList(): Promise<Company[]>;
  
  // Vehicle operations
  createVehicle(vehicleData: InsertVehicle): Promise<Vehicle>;
  getVehicleById(id: string): Promise<Vehicle | undefined>;
  getVehiclesByOwner(ownerId: string): Promise<Vehicle[]>;
  getVehiclesByCompany(companyId: string): Promise<Vehicle[]>;
  getPendingVehicles(): Promise<Vehicle[]>;
  approveVehicle(vehicleId: string, adminId: string): Promise<Vehicle>;
  rejectVehicle(vehicleId: string, adminId: string, reason: string): Promise<Vehicle>;
  
  // Document operations
  createDocument(data: { vehicleId: string; documentType: string; fileName: string; filePath: string; fileSize?: number; mimeType?: string }): Promise<any>;
  getDocumentsByVehicle(vehicleId: string): Promise<any[]>;
  
  // Transaction operations
  createTransaction(transactionData: InsertTransaction): Promise<Transaction>;
  getUserTransactions(userId: string): Promise<Transaction[]>;
  getTransactionById(id: string): Promise<Transaction | undefined>;
  
  // Additional methods for transaction functionality
  getUserVehicles(userId: string): Promise<Vehicle[]>;
  getActiveMerchants(): Promise<Merchant[]>;
  getUserByEmail(email: string): Promise<User | undefined>;
  
  // Fuel balance operations
  getFuelBalance(userId: string, fuelType: string): Promise<FuelBalance | undefined>;
  updateFuelBalance(userId: string, fuelType: string, amount: number): Promise<FuelBalance>;
  initializeFuelBalances(userId: string): Promise<void>;
  
  // Transaction operations
  createTransaction(data: InsertTransaction): Promise<Transaction>;
  getTransactionById(id: string): Promise<Transaction | undefined>;
  getTransactionsByUser(userId: string, limit?: number): Promise<Transaction[]>;
  getTransactionsByMerchant(merchantId: string, limit?: number): Promise<Transaction[]>;
  updateTransactionStatus(id: string, status: string, completedAt?: Date): Promise<Transaction>;
  
  // Transaction limits
  getTransactionLimits(userId: string): Promise<TransactionLimit | undefined>;
  setTransactionLimits(userId: string, userType: string, limits: Partial<TransactionLimit>): Promise<TransactionLimit>;
  
  // Coupon operations
  createCoupon(data: InsertCoupon): Promise<Coupon>;
  getCouponByCode(code: string): Promise<Coupon | undefined>;
  getActiveCoupons(limit?: number): Promise<Coupon[]>;
  redeemCoupon(code: string, userId: string): Promise<Coupon>;
  deactivateCoupon(id: string): Promise<Coupon>;
  generateUniqueCouponCode(fuelType: string, amount: number): Promise<string>;
  
  // Merchant operations
  createMerchant(data: InsertMerchant): Promise<Merchant>;
  getMerchantById(id: string): Promise<Merchant | undefined>;
  getMerchantByUserId(userId: string): Promise<Merchant | undefined>;
  getMerchantsList(): Promise<Merchant[]>;
  updateMerchantBalance(merchantId: string, amount: number): Promise<Merchant>;
  
  // Withdrawal operations
  createWithdrawalRequest(merchantId: string, amount: number): Promise<WithdrawalRequest>;
  getWithdrawalRequests(status?: string): Promise<WithdrawalRequest[]>;
  updateWithdrawalStatus(id: string, status: string, processedBy: string, notes?: string): Promise<WithdrawalRequest>;
  
  // Admin operations
  createAdminRole(userId: string, role: string, permissions: any): Promise<AdminRole>;
  getAdminRole(userId: string): Promise<AdminRole | undefined>;
  updateAdminPermissions(userId: string, permissions: any): Promise<AdminRole>;
  
  // Statistics
  getSystemStats(): Promise<{
    totalUsers: number;
    corporateFleets: number;
    pendingVehicles: number;
    activeCoupons: number;
    totalRevenue: number;
    monthlyVolume: number;
  }>;
}

export class DatabaseStorage implements IStorage {
  // User operations
  async getUser(id: string): Promise<User | undefined> {
    const [user] = await db.select().from(users).where(eq(users.id, id));
    return user;
  }

  async upsertUser(userData: UpsertUser): Promise<User> {
    const [user] = await db
      .insert(users)
      .values(userData)
      .onConflictDoUpdate({
        target: users.id,
        set: {
          ...userData,
          updatedAt: new Date(),
        },
      })
      .returning();
    
    // Initialize fuel balances for new users
    if (user) {
      await this.initializeFuelBalances(user.id);
    }
    
    return user;
  }

  // Company operations
  async createCompany(data: { name: string; registrationNumber?: string; address?: string; contactEmail?: string; contactPhone?: string }): Promise<Company> {
    const [company] = await db.insert(companies).values(data).returning();
    return company;
  }

  async getCompanyById(id: string): Promise<Company | undefined> {
    const [company] = await db.select().from(companies).where(eq(companies.id, id));
    return company;
  }

  async getCompaniesList(): Promise<Company[]> {
    return await db.select().from(companies).orderBy(desc(companies.createdAt));
  }

  // Vehicle operations
  async createVehicle(vehicleData: InsertVehicle): Promise<Vehicle> {
    const [vehicle] = await db.insert(vehicles).values(vehicleData).returning();
    return vehicle;
  }

  async getVehicleById(id: string): Promise<Vehicle | undefined> {
    const [vehicle] = await db.select().from(vehicles).where(eq(vehicles.id, id));
    return vehicle;
  }

  async getVehiclesByOwner(ownerId: string): Promise<Vehicle[]> {
    return await db.select().from(vehicles).where(eq(vehicles.ownerId, ownerId)).orderBy(desc(vehicles.createdAt));
  }

  async getVehiclesByCompany(companyId: string): Promise<Vehicle[]> {
    return await db.select().from(vehicles).where(eq(vehicles.companyId, companyId)).orderBy(desc(vehicles.createdAt));
  }

  async getPendingVehicles(): Promise<Vehicle[]> {
    return await db.select().from(vehicles).where(eq(vehicles.status, 'pending')).orderBy(desc(vehicles.createdAt));
  }

  async approveVehicle(vehicleId: string, adminId: string): Promise<Vehicle> {
    const [vehicle] = await db
      .update(vehicles)
      .set({
        status: 'approved',
        approvedAt: new Date(),
        approvedBy: adminId,
      })
      .where(eq(vehicles.id, vehicleId))
      .returning();
    return vehicle;
  }

  async rejectVehicle(vehicleId: string, adminId: string, reason: string): Promise<Vehicle> {
    const [vehicle] = await db
      .update(vehicles)
      .set({
        status: 'rejected',
        approvedBy: adminId,
        rejectionReason: reason,
      })
      .where(eq(vehicles.id, vehicleId))
      .returning();
    return vehicle;
  }

  // Document operations
  async createDocument(data: { vehicleId: string; documentType: string; fileName: string; filePath: string; fileSize?: number; mimeType?: string }): Promise<any> {
    const [document] = await db.insert(documents).values(data).returning();
    return document;
  }

  async getDocumentsByVehicle(vehicleId: string): Promise<any[]> {
    return await db.select().from(documents).where(eq(documents.vehicleId, vehicleId));
  }

  // Fuel balance operations
  async getFuelBalance(userId: string, fuelType: string): Promise<FuelBalance | undefined> {
    const [balance] = await db
      .select()
      .from(fuelBalances)
      .where(and(eq(fuelBalances.userId, userId), eq(fuelBalances.fuelType, fuelType)));
    return balance;
  }

  async updateFuelBalance(userId: string, fuelType: string, amount: number): Promise<FuelBalance> {
    const [balance] = await db
      .insert(fuelBalances)
      .values({
        userId,
        fuelType,
        balance: amount.toString(),
      })
      .onConflictDoUpdate({
        target: [fuelBalances.userId, fuelBalances.fuelType],
        set: {
          balance: sql`${fuelBalances.balance} + ${amount}`,
          updatedAt: new Date(),
        },
      })
      .returning();
    return balance;
  }

  async initializeFuelBalances(userId: string): Promise<void> {
    const fuelTypes = ['petrol', 'diesel'];
    for (const fuelType of fuelTypes) {
      await db
        .insert(fuelBalances)
        .values({
          userId,
          fuelType,
          balance: '0.00',
        })
        .onConflictDoNothing();
    }
  }

  // Transaction operations
  async createTransaction(data: InsertTransaction): Promise<Transaction> {
    const [transaction] = await db.insert(transactions).values(data).returning();
    return transaction;
  }

  async getTransactionById(id: string): Promise<Transaction | undefined> {
    const [transaction] = await db.select().from(transactions).where(eq(transactions.id, id));
    return transaction;
  }

  async getTransactionsByUser(userId: string, limit: number = 50): Promise<Transaction[]> {
    return await db
      .select()
      .from(transactions)
      .where(eq(transactions.userId, userId))
      .orderBy(desc(transactions.createdAt))
      .limit(limit);
  }

  async getUserTransactions(userId: string): Promise<Transaction[]> {
    return this.getTransactionsByUser(userId, 20);
  }

  async getUserVehicles(userId: string): Promise<Vehicle[]> {
    return await db
      .select()
      .from(vehicles)
      .where(eq(vehicles.ownerId, userId))
      .orderBy(desc(vehicles.createdAt));
  }

  async getActiveMerchants(): Promise<Merchant[]> {
    return await db
      .select()
      .from(merchants)
      .where(eq(merchants.status, 'active'))
      .orderBy(merchants.stationName);
  }

  async getUserByEmail(email: string): Promise<User | undefined> {
    const [user] = await db
      .select()
      .from(users)
      .where(eq(users.email, email));
    return user;
  }

  async getTransactionsByMerchant(merchantId: string, limit: number = 50): Promise<Transaction[]> {
    return await db
      .select()
      .from(transactions)
      .where(eq(transactions.userId, userId))
      .orderBy(desc(transactions.createdAt))
      .limit(limit);
  }

  async getTransactionsByMerchant(merchantId: string, limit: number = 50): Promise<Transaction[]> {
    return await db
      .select()
      .from(transactions)
      .where(eq(transactions.merchantId, merchantId))
      .orderBy(desc(transactions.createdAt))
      .limit(limit);
  }

  async updateTransactionStatus(id: string, status: string, completedAt?: Date): Promise<Transaction> {
    const [transaction] = await db
      .update(transactions)
      .set({
        status,
        completedAt,
      })
      .where(eq(transactions.id, id))
      .returning();
    return transaction;
  }

  // Transaction limits
  async getTransactionLimits(userId: string): Promise<TransactionLimit | undefined> {
    const [limits] = await db.select().from(transactionLimits).where(eq(transactionLimits.userId, userId));
    return limits;
  }

  async setTransactionLimits(userId: string, userType: string, limits: Partial<TransactionLimit>): Promise<TransactionLimit> {
    const [limit] = await db
      .insert(transactionLimits)
      .values({
        userId,
        userType,
        ...limits,
      })
      .onConflictDoUpdate({
        target: transactionLimits.userId,
        set: {
          ...limits,
          updatedAt: new Date(),
        },
      })
      .returning();
    return limit;
  }

  // Coupon operations
  async createCoupon(data: InsertCoupon): Promise<Coupon> {
    const code = await this.generateUniqueCouponCode(data.fuelType, parseFloat(data.amount));
    const [coupon] = await db.insert(coupons).values({ ...data, code }).returning();
    return coupon;
  }

  async getCouponByCode(code: string): Promise<Coupon | undefined> {
    const [coupon] = await db.select().from(coupons).where(eq(coupons.code, code));
    return coupon;
  }

  async getActiveCoupons(limit: number = 100): Promise<Coupon[]> {
    return await db
      .select()
      .from(coupons)
      .where(eq(coupons.status, 'active'))
      .orderBy(desc(coupons.createdAt))
      .limit(limit);
  }

  async redeemCoupon(code: string, userId: string): Promise<Coupon> {
    const [coupon] = await db
      .update(coupons)
      .set({
        status: 'used',
        usedAt: new Date(),
        usedBy: userId,
      })
      .where(and(eq(coupons.code, code), eq(coupons.status, 'active')))
      .returning();
    
    if (!coupon) {
      throw new Error('Coupon not found or already used');
    }
    
    return coupon;
  }

  async deactivateCoupon(id: string): Promise<Coupon> {
    const [coupon] = await db
      .update(coupons)
      .set({ status: 'deactivated' })
      .where(eq(coupons.id, id))
      .returning();
    return coupon;
  }

  async generateUniqueCouponCode(fuelType: string, amount: number): Promise<string> {
    const prefix = fuelType === 'petrol' ? 'PET' : 'DSL';
    let isUnique = false;
    let code = '';
    
    while (!isUnique) {
      const randomPart = Math.random().toString(36).substring(2, 8).toUpperCase();
      code = `FUEL-${prefix}-${amount}L-${randomPart}`;
      
      const existing = await this.getCouponByCode(code);
      if (!existing) {
        isUnique = true;
      }
    }
    
    return code;
  }

  // Merchant operations
  async createMerchant(data: InsertMerchant): Promise<Merchant> {
    const [merchant] = await db.insert(merchants).values(data).returning();
    return merchant;
  }

  async getMerchantById(id: string): Promise<Merchant | undefined> {
    const [merchant] = await db.select().from(merchants).where(eq(merchants.id, id));
    return merchant;
  }

  async getMerchantByUserId(userId: string): Promise<Merchant | undefined> {
    const [merchant] = await db.select().from(merchants).where(eq(merchants.userId, userId));
    return merchant;
  }

  async getMerchantsList(): Promise<Merchant[]> {
    return await db.select().from(merchants).orderBy(desc(merchants.createdAt));
  }

  async updateMerchantBalance(merchantId: string, amount: number): Promise<Merchant> {
    const [merchant] = await db
      .update(merchants)
      .set({
        pendingBalance: sql`${merchants.pendingBalance} + ${amount}`,
        updatedAt: new Date(),
      })
      .where(eq(merchants.id, merchantId))
      .returning();
    return merchant;
  }

  // Withdrawal operations
  async createWithdrawalRequest(merchantId: string, amount: number): Promise<WithdrawalRequest> {
    const [request] = await db.insert(withdrawalRequests).values({
      merchantId,
      amount: amount.toString(),
    }).returning();
    return request;
  }

  async getWithdrawalRequests(status?: string): Promise<WithdrawalRequest[]> {
    const query = db.select().from(withdrawalRequests);
    
    if (status) {
      return await query.where(eq(withdrawalRequests.status, status)).orderBy(desc(withdrawalRequests.requestedAt));
    }
    
    return await query.orderBy(desc(withdrawalRequests.requestedAt));
  }

  async updateWithdrawalStatus(id: string, status: string, processedBy: string, notes?: string): Promise<WithdrawalRequest> {
    const [request] = await db
      .update(withdrawalRequests)
      .set({
        status,
        processedAt: new Date(),
        processedBy,
        notes,
      })
      .where(eq(withdrawalRequests.id, id))
      .returning();
    return request;
  }

  // Admin operations
  async createAdminRole(userId: string, role: string, permissions: any): Promise<AdminRole> {
    const [adminRole] = await db
      .insert(adminRoles)
      .values({
        userId,
        role,
        permissions,
      })
      .onConflictDoUpdate({
        target: adminRoles.userId,
        set: {
          role,
          permissions,
          updatedAt: new Date(),
        },
      })
      .returning();
    return adminRole;
  }

  async getAdminRole(userId: string): Promise<AdminRole | undefined> {
    const [role] = await db.select().from(adminRoles).where(eq(adminRoles.userId, userId));
    return role;
  }

  async updateAdminPermissions(userId: string, permissions: any): Promise<AdminRole> {
    const [role] = await db
      .update(adminRoles)
      .set({
        permissions,
        updatedAt: new Date(),
      })
      .where(eq(adminRoles.userId, userId))
      .returning();
    return role;
  }

  // Statistics
  async getSystemStats(): Promise<{
    totalUsers: number;
    corporateFleets: number;
    pendingVehicles: number;
    activeCoupons: number;
    totalRevenue: number;
    monthlyVolume: number;
  }> {
    const [userCount] = await db.select({ count: count() }).from(users);
    const [companyCount] = await db.select({ count: count() }).from(companies);
    const [pendingCount] = await db.select({ count: count() }).from(vehicles).where(eq(vehicles.status, 'pending'));
    const [couponCount] = await db.select({ count: count() }).from(coupons).where(eq(coupons.status, 'active'));
    
    // Get current month's transactions
    const currentMonth = new Date();
    currentMonth.setDate(1);
    currentMonth.setHours(0, 0, 0, 0);
    
    const [revenueResult] = await db
      .select({ total: sum(transactions.monetaryValue) })
      .from(transactions)
      .where(and(
        eq(transactions.status, 'completed'),
        gte(transactions.createdAt, currentMonth)
      ));
    
    const [volumeResult] = await db
      .select({ total: sum(transactions.amount) })
      .from(transactions)
      .where(and(
        eq(transactions.transactionType, 'fuel_usage'),
        eq(transactions.status, 'completed'),
        gte(transactions.createdAt, currentMonth)
      ));
    
    return {
      totalUsers: userCount.count,
      corporateFleets: companyCount.count,
      pendingVehicles: pendingCount.count,
      activeCoupons: couponCount.count,
      totalRevenue: parseFloat(revenueResult.total || '0'),
      monthlyVolume: parseFloat(volumeResult.total || '0'),
    };
  }
}

export const storage = new DatabaseStorage();
