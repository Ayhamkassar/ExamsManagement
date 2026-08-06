import { useState } from "react";
import {
  BarChart, Bar, AreaChart, Area, RadarChart, Radar, PolarGrid,
  PolarAngleAxis, XAxis, YAxis, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell,
} from "recharts";
import {
  LayoutDashboard, FileText, Users, GraduationCap, BarChart2, Settings,
  Bell, Search, Plus, ChevronRight, CheckCircle, Clock, AlertTriangle,
  TrendingUp, TrendingDown, Star, Shield, Zap, Globe, ArrowRight, Check,
  MessageSquare, Upload, Eye, Edit3, ZoomIn, Award, BookOpen, Calendar,
  Download, MoreHorizontal, Building2, User, Save, Menu, X, Filter,
  Layers, Lock, Pencil, Clipboard, Home,
} from "lucide-react";

type View = "landing" | "admin" | "teacher" | "student";
type AdminSection = "overview" | "exams" | "students" | "analytics";
type StudentTab = "dashboard" | "results" | "appeals";

// ─── Utility ─────────────────────────────────────────────────────────────────

function cn(...classes: (string | false | undefined | null)[]) {
  return classes.filter(Boolean).join(" ");
}

// ─── Data ─────────────────────────────────────────────────────────────────────

const EXAMS = [
  { id: 1, name: "Mathematics Advanced", subject: "Mathematics", date: "Aug 15, 2026", students: 142, status: "scheduled", teacher: "Dr. Chen Wei" },
  { id: 2, name: "English Literature Final", subject: "English", date: "Aug 10, 2026", students: 98, status: "grading", teacher: "Prof. Sarah Mills" },
  { id: 3, name: "Physics Fundamentals", subject: "Physics", date: "Jul 28, 2026", students: 115, status: "published", teacher: "Dr. Amir Hassan" },
  { id: 4, name: "Chemistry Lab Assessment", subject: "Chemistry", date: "Jul 20, 2026", students: 87, status: "published", teacher: "Dr. Elena Kovacs" },
  { id: 5, name: "History of Modern Europe", subject: "History", date: "Aug 22, 2026", students: 76, status: "scheduled", teacher: "Prof. James Okafor" },
];

const STUDENTS = [
  { id: 1, name: "Sophia Chen", idNo: "STU-2847", cls: "Year 12A", gpa: 3.9, exams: 5 },
  { id: 2, name: "Marcus Williams", idNo: "STU-3192", cls: "Year 12B", gpa: 3.5, exams: 5 },
  { id: 3, name: "Aisha Patel", idNo: "STU-2953", cls: "Year 12A", gpa: 4.0, exams: 5 },
  { id: 4, name: "Lucas Fernandez", idNo: "STU-3401", cls: "Year 11C", gpa: 3.2, exams: 4 },
  { id: 5, name: "Emma Thompson", idNo: "STU-2776", cls: "Year 12B", gpa: 3.7, exams: 5 },
  { id: 6, name: "Omar Al-Rashidi", idNo: "STU-3018", cls: "Year 11A", gpa: 3.6, exams: 4 },
];

const perfData = [
  { month: "Mar", avg: 72, top: 91 },
  { month: "Apr", avg: 74, top: 93 },
  { month: "May", avg: 71, top: 89 },
  { month: "Jun", avg: 78, top: 96 },
  { month: "Jul", avg: 76, top: 94 },
  { month: "Aug", avg: 80, top: 97 },
];

const gradeData = [
  { grade: "A+", count: 18 },
  { grade: "A",  count: 24 },
  { grade: "B+", count: 22 },
  { grade: "B",  count: 19 },
  { grade: "C+", count: 12 },
  { grade: "C",  count: 8  },
  { grade: "D",  count: 5  },
];

const pieData = [
  { name: "A / A+", value: 42, fill: "#3730A3" },
  { name: "B / B+", value: 41, fill: "#818CF8" },
  { name: "C / C+", value: 20, fill: "#C7D2FE" },
  { name: "D / Below", value: 5, fill: "#E0E7FF" },
];

const radarData = [
  { subject: "Math",    score: 85, fullMark: 100 },
  { subject: "English", score: 72, fullMark: 100 },
  { subject: "Physics", score: 90, fullMark: 100 },
  { subject: "Chem",    score: 68, fullMark: 100 },
  { subject: "History", score: 78, fullMark: 100 },
];

const QUESTIONS = [
  { no: 1, text: "Solve: 3x² + 7x − 6 = 0", max: 6, awarded: 5, comment: "Correct method, minor arithmetic slip on last step" },
  { no: 2, text: "Differentiate f(x) = (2x³ − 5x)·sin(x) using the product rule", max: 8, awarded: 7, comment: "Good product rule application" },
  { no: 3, text: "Evaluate the definite integral ∫₀³(4x² + 3x − 2)dx", max: 8, awarded: 6, comment: "Integration correct, limits evaluation error" },
  { no: 4, text: "Prove lim(x→∞) (x²+1)/(2x²−x) = 1/2 using L'Hôpital's rule", max: 10, awarded: 9, comment: "Excellent proof structure" },
  { no: 5, text: "Sketch y = x³ − 6x² + 9x + 1 and find all stationary points", max: 8, awarded: 8, comment: "Perfect analysis" },
];

const RESULTS = [
  { exam: "Physics Fundamentals", date: "Jul 28, 2026", score: 87, max: 100, grade: "A−", status: "published" },
  { exam: "Chemistry Lab Assessment", date: "Jul 20, 2026", score: 74, max: 100, grade: "B+", status: "published" },
  { exam: "English Literature Final", date: "Aug 10, 2026", score: 81, max: 100, grade: "A−", status: "grading" },
];

const APPEALS = [
  { id: "APL-0041", exam: "Physics Fundamentals", date: "Aug 2, 2026", status: "under_review", reason: "Partial credit not awarded for Q4 calculation step — method was correct" },
  { id: "APL-0038", exam: "Chemistry Lab", date: "Jul 25, 2026", status: "accepted", reason: "Alternative valid approach accepted after second-reader review" },
];

const PRICING = [
  {
    name: "Starter", price: 49, period: "/month",
    desc: "For small institutions getting started with digital exams",
    features: ["Up to 200 students", "5 GB storage", "Basic analytics", "Email support", "10 exams/month", "Digital correction"],
    cta: "Start Free Trial", highlight: false,
  },
  {
    name: "Professional", price: 149, period: "/month",
    desc: "Everything growing schools need for seamless exam management",
    features: ["Up to 2,000 students", "100 GB storage", "Advanced analytics", "Priority support", "Unlimited exams", "Custom branding", "Appeal management", "Audit trail"],
    cta: "Start Free Trial", highlight: true,
  },
  {
    name: "Enterprise", price: null, period: "",
    desc: "Full-scale deployment for universities and large organizations",
    features: ["Unlimited students", "Unlimited storage", "Custom analytics", "Dedicated support", "SSO & LDAP", "SLA guarantee", "API access", "Custom integrations"],
    cta: "Contact Sales", highlight: false,
  },
];

const FEATURES = [
  { Icon: Clipboard,   title: "Exam Creation",       desc: "Build structured exams with question banks, time limits, and automatic answer randomization." },
  { Icon: Edit3,       title: "Digital Grading",     desc: "Annotate scanned and digital answer sheets with scoring tools, rubrics, and a full audit trail." },
  { Icon: Eye,         title: "Paper Transparency",  desc: "Students view their corrected papers with per-question marks, comments, and correction history." },
  { Icon: BarChart2,   title: "Performance Analytics", desc: "Track cohort progress, surface weak areas, and generate exportable reports in seconds." },
  { Icon: MessageSquare, title: "Appeal Workflow",   desc: "Structured digital appeals with automatic routing to reviewers and real-time status tracking." },
  { Icon: Shield,      title: "Security & Compliance", desc: "Role-based access, full audit logs, and data encrypted at rest and in transit — ISO 27001 ready." },
];

const TESTIMONIALS = [
  { name: "Dr. Patricia Mensah", role: "Director of Academic Affairs", org: "Accra Business School", quote: "ExamFlow reduced our grading cycle from 3 weeks to 4 days. The audit trail alone transformed how we handle grade disputes.", stars: 5 },
  { name: "Prof. Rajesh Kumar", role: "VP Technology", org: "Pinnacle University", quote: "We serve 8,000 students across three campuses. The analytics dashboard gives us insights we simply never had with paper exams.", stars: 5 },
  { name: "Sofia Marchetti", role: "Head of Education", org: "EuroTech Training Institute", quote: "The appeal management module alone justified the investment. Students trust the process because it is fully transparent.", stars: 5 },
];

// ─── Primitives ───────────────────────────────────────────────────────────────

function Badge({ children, variant = "default" }: { children: React.ReactNode; variant?: "default" | "success" | "warning" | "danger" | "info" | "violet" }) {
  const s = {
    default: "bg-secondary text-secondary-foreground",
    success: "bg-emerald-50 text-emerald-700",
    warning: "bg-amber-50 text-amber-700",
    danger:  "bg-red-50 text-red-600",
    info:    "bg-sky-50 text-sky-700",
    violet:  "bg-violet-50 text-violet-700",
  }[variant];
  return <span className={cn("inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold", s)}>{children}</span>;
}

function Btn({ children, variant = "primary", size = "md", className, onClick }: {
  children: React.ReactNode;
  variant?: "primary" | "secondary" | "ghost" | "outline" | "white";
  size?: "xs" | "sm" | "md" | "lg";
  className?: string;
  onClick?: () => void;
}) {
  const v = {
    primary:   "bg-primary text-white hover:bg-indigo-900 shadow-sm",
    secondary: "bg-secondary text-primary hover:bg-indigo-100",
    ghost:     "hover:bg-muted text-foreground",
    outline:   "border border-border bg-transparent hover:bg-muted text-foreground",
    white:     "bg-white text-primary hover:bg-indigo-50 shadow-sm",
  }[variant];
  const s = {
    xs: "h-7 px-2.5 text-xs rounded-md",
    sm: "h-8 px-3.5 text-sm rounded-lg",
    md: "h-9 px-4 text-sm rounded-lg",
    lg: "h-11 px-6 text-base rounded-xl font-semibold",
  }[size];
  return (
    <button onClick={onClick} className={cn("inline-flex items-center gap-1.5 font-medium transition-all cursor-pointer flex-shrink-0", v, s, className)}>
      {children}
    </button>
  );
}

function Avatar({ name, size = "md" }: { name: string; size?: "xs" | "sm" | "md" | "lg" }) {
  const initials = name.split(" ").slice(0, 2).map(n => n[0]).join("").toUpperCase();
  const palette = ["bg-indigo-600", "bg-violet-600", "bg-sky-600", "bg-emerald-600", "bg-amber-600", "bg-rose-600"];
  const bg = palette[name.charCodeAt(0) % palette.length];
  const sz = { xs: "w-6 h-6 text-[10px]", sm: "w-8 h-8 text-xs", md: "w-9 h-9 text-sm", lg: "w-11 h-11 text-base" }[size];
  return <div className={cn("rounded-full flex items-center justify-center font-semibold text-white flex-shrink-0", bg, sz)}>{initials}</div>;
}

function StatusBadge({ status }: { status: string }) {
  const map: Record<string, { label: string; variant: "success" | "warning" | "info" | "danger" | "default" }> = {
    published:    { label: "Published",    variant: "success"  },
    grading:      { label: "Grading",      variant: "warning"  },
    scheduled:    { label: "Scheduled",    variant: "info"     },
    active:       { label: "Active",       variant: "success"  },
    under_review: { label: "Under Review", variant: "warning"  },
    accepted:     { label: "Accepted",     variant: "success"  },
    rejected:     { label: "Rejected",     variant: "danger"   },
  };
  const e = map[status] ?? { label: status, variant: "default" as const };
  return <Badge variant={e.variant}>{e.label}</Badge>;
}

function Card({ children, className }: { children: React.ReactNode; className?: string }) {
  return <div className={cn("bg-card border border-border rounded-xl", className)}>{children}</div>;
}

function StatCard({ label, value, delta, Icon, accent = "indigo" }: {
  label: string; value: string;
  delta?: { label: string; up: boolean };
  Icon: React.ElementType;
  accent?: "indigo" | "emerald" | "sky" | "amber" | "violet";
}) {
  const a = {
    indigo: "bg-indigo-50 text-indigo-600",
    emerald: "bg-emerald-50 text-emerald-600",
    sky: "bg-sky-50 text-sky-600",
    amber: "bg-amber-50 text-amber-600",
    violet: "bg-violet-50 text-violet-600",
  }[accent];
  return (
    <Card className="p-5">
      <div className="flex items-start justify-between mb-3">
        <div className={cn("p-2.5 rounded-lg", a)}><Icon className="w-4 h-4" /></div>
        {delta && (
          <span className={cn("text-xs font-medium flex items-center gap-0.5", delta.up ? "text-emerald-600" : "text-red-500")}>
            {delta.up ? <TrendingUp className="w-3 h-3" /> : <TrendingDown className="w-3 h-3" />}
            {delta.label}
          </span>
        )}
      </div>
      <div className="text-2xl font-bold text-foreground mb-0.5" style={{ fontFamily: "var(--font-heading)" }}>{value}</div>
      <div className="text-xs text-muted-foreground">{label}</div>
    </Card>
  );
}

// ─── Landing Page ─────────────────────────────────────────────────────────────

function LandingNav({ onNav }: { onNav: (v: View) => void }) {
  return (
    <header className="fixed top-0 left-0 right-0 z-40 bg-[#0A0B1E]/90 backdrop-blur-md border-b border-white/[0.06]">
      <div className="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
            <Layers className="w-4 h-4 text-white" />
          </div>
          <span className="font-bold text-white text-lg" style={{ fontFamily: "var(--font-heading)" }}>ExamFlow</span>
        </div>
        <nav className="hidden md:flex items-center gap-6">
          {["Features", "Pricing", "Customers", "Docs"].map(l => (
            <a key={l} className="text-sm text-white/60 hover:text-white transition-colors cursor-pointer">{l}</a>
          ))}
        </nav>
        <div className="flex items-center gap-3">
          <button className="text-sm text-white/70 hover:text-white transition-colors cursor-pointer hidden sm:block">Sign in</button>
          <Btn variant="white" size="sm" onClick={() => onNav("admin")}>Get Started <ArrowRight className="w-3.5 h-3.5" /></Btn>
        </div>
      </div>
    </header>
  );
}

function HeroSection({ onNav }: { onNav: (v: View) => void }) {
  return (
    <section className="relative min-h-screen bg-[#0A0B1E] flex flex-col items-center justify-center overflow-hidden pt-16">
      {/* Gradient orbs */}
      <div className="absolute top-1/4 left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full bg-indigo-600/20 blur-[120px] pointer-events-none" />
      <div className="absolute bottom-1/3 right-1/4 w-[400px] h-[400px] rounded-full bg-violet-600/15 blur-[100px] pointer-events-none" />

      <div className="relative z-10 text-center px-6 max-w-5xl mx-auto">
        <div className="inline-flex items-center gap-2 bg-white/[0.06] border border-white/[0.1] text-indigo-300 text-xs font-medium px-3.5 py-1.5 rounded-full mb-8">
          <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
          Now trusted by 400+ institutions worldwide
        </div>

        <h1 className="text-5xl md:text-7xl font-extrabold text-white leading-[1.08] tracking-tight mb-6" style={{ fontFamily: "var(--font-heading)" }}>
          The Future of{" "}
          <span className="bg-gradient-to-r from-indigo-400 via-violet-400 to-sky-400 bg-clip-text text-transparent">
            Examination
          </span>{" "}
          Management
        </h1>

        <p className="text-lg md:text-xl text-white/55 max-w-2xl mx-auto leading-relaxed mb-10">
          ExamFlow digitizes the entire exam lifecycle — creation, grading, results, and appeals — giving institutions the transparency and analytics they need to thrive.
        </p>

        <div className="flex flex-col sm:flex-row items-center justify-center gap-3 mb-16">
          <Btn variant="primary" size="lg" className="!bg-indigo-600 hover:!bg-indigo-500 min-w-[180px]" onClick={() => onNav("admin")}>
            Start Free Trial <ArrowRight className="w-4 h-4" />
          </Btn>
          <Btn variant="outline" size="lg" className="!border-white/20 !text-white hover:!bg-white/[0.06] min-w-[160px]" onClick={() => onNav("teacher")}>
            <Eye className="w-4 h-4" /> See it in Action
          </Btn>
        </div>

        {/* Dashboard mockup */}
        <div className="relative mx-auto max-w-4xl">
          <div className="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-[#0A0B1E] z-10 pointer-events-none" style={{ top: "60%" }} />
          <div className="rounded-2xl border border-white/[0.08] bg-white/[0.03] overflow-hidden shadow-2xl shadow-indigo-900/40">
            {/* Fake browser chrome */}
            <div className="bg-white/[0.04] border-b border-white/[0.06] px-4 py-3 flex items-center gap-2">
              <div className="flex gap-1.5">
                <span className="w-3 h-3 rounded-full bg-rose-500/60" />
                <span className="w-3 h-3 rounded-full bg-amber-500/60" />
                <span className="w-3 h-3 rounded-full bg-emerald-500/60" />
              </div>
              <div className="flex-1 mx-4 bg-white/[0.06] rounded-md h-6 flex items-center px-3">
                <span className="text-white/30 text-xs">app.examflow.io/dashboard</span>
              </div>
            </div>
            {/* Mini dashboard preview */}
            <div className="p-5 grid grid-cols-4 gap-3">
              {[
                { label: "Total Students", value: "1,247", color: "bg-indigo-500/20 text-indigo-300" },
                { label: "Exams This Month", value: "34", color: "bg-violet-500/20 text-violet-300" },
                { label: "Avg Score", value: "76.4%", color: "bg-sky-500/20 text-sky-300" },
                { label: "Pending Appeals", value: "7", color: "bg-amber-500/20 text-amber-300" },
              ].map(s => (
                <div key={s.label} className="bg-white/[0.04] rounded-xl p-3.5 border border-white/[0.06]">
                  <div className={cn("text-xs font-semibold px-2 py-0.5 rounded-md mb-2 inline-block", s.color)}>{s.label}</div>
                  <div className="text-white text-xl font-bold" style={{ fontFamily: "var(--font-heading)" }}>{s.value}</div>
                </div>
              ))}
            </div>
            <div className="px-5 pb-5">
              <div className="bg-white/[0.03] rounded-xl border border-white/[0.06] p-4 h-24 flex items-end gap-1.5">
                {[40, 65, 55, 70, 80, 60, 75, 85, 72, 88, 76, 90].map((h, i) => (
                  <div key={i} className="flex-1 rounded-sm bg-indigo-500/40" style={{ height: `${h}%` }} />
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

function FeaturesSection() {
  return (
    <section className="py-24 bg-white">
      <div className="max-w-7xl mx-auto px-6">
        <div className="text-center mb-16">
          <Badge variant="violet">Platform Features</Badge>
          <h2 className="mt-4 text-4xl font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>
            Everything your institution needs
          </h2>
          <p className="mt-3 text-lg text-muted-foreground max-w-xl mx-auto">
            From first question to final result — ExamFlow handles the entire process with transparency and precision.
          </p>
        </div>
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {FEATURES.map(({ Icon, title, desc }, i) => (
            <div key={title} className="group p-6 rounded-2xl border border-border hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-100/50 transition-all bg-background">
              <div className="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <Icon className="w-5 h-5 text-white" />
              </div>
              <h3 className="font-semibold text-foreground mb-2" style={{ fontFamily: "var(--font-heading)" }}>{title}</h3>
              <p className="text-sm text-muted-foreground leading-relaxed">{desc}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

function HowItWorksSection() {
  const steps = [
    { no: "01", title: "Create & Schedule", desc: "Build your exam, assign invigilators, and schedule it with a few clicks. Students get automatic notifications." },
    { no: "02", title: "Grade & Annotate", desc: "Teachers grade digital papers with annotation tools. Every mark change is logged with timestamp and identity." },
    { no: "03", title: "Publish & Review", desc: "Publish results instantly. Students review their corrected papers and submit appeals if needed — all digitally." },
  ];
  return (
    <section className="py-24 bg-background">
      <div className="max-w-7xl mx-auto px-6">
        <div className="text-center mb-16">
          <Badge variant="info">How It Works</Badge>
          <h2 className="mt-4 text-4xl font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Three steps. Zero paperwork.</h2>
        </div>
        <div className="grid md:grid-cols-3 gap-8 relative">
          <div className="hidden md:block absolute top-12 left-1/3 right-1/3 h-px bg-gradient-to-r from-indigo-200 to-indigo-200" />
          {steps.map(({ no, title, desc }) => (
            <div key={no} className="text-center relative">
              <div className="w-16 h-16 rounded-2xl bg-primary text-white flex items-center justify-center text-xl font-bold mx-auto mb-5 relative z-10" style={{ fontFamily: "var(--font-heading)" }}>
                {no}
              </div>
              <h3 className="font-semibold text-foreground text-lg mb-2" style={{ fontFamily: "var(--font-heading)" }}>{title}</h3>
              <p className="text-sm text-muted-foreground leading-relaxed max-w-xs mx-auto">{desc}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

function PricingSection() {
  return (
    <section className="py-24 bg-white">
      <div className="max-w-7xl mx-auto px-6">
        <div className="text-center mb-16">
          <Badge variant="success">Pricing</Badge>
          <h2 className="mt-4 text-4xl font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Simple, transparent pricing</h2>
          <p className="mt-3 text-muted-foreground">Start free for 30 days. No credit card required.</p>
        </div>
        <div className="grid md:grid-cols-3 gap-6 items-start max-w-5xl mx-auto">
          {PRICING.map(plan => (
            <div key={plan.name} className={cn(
              "rounded-2xl p-7 border relative",
              plan.highlight
                ? "bg-primary border-primary shadow-xl shadow-indigo-900/20 scale-[1.02]"
                : "bg-background border-border"
            )}>
              {plan.highlight && (
                <div className="absolute -top-3.5 left-1/2 -translate-x-1/2">
                  <span className="bg-gradient-to-r from-indigo-400 to-violet-400 text-white text-xs font-bold px-4 py-1 rounded-full">Most Popular</span>
                </div>
              )}
              <div className={cn("text-sm font-semibold mb-1", plan.highlight ? "text-indigo-200" : "text-muted-foreground")}>{plan.name}</div>
              <div className="flex items-baseline gap-1 mb-3">
                {plan.price ? (
                  <>
                    <span className={cn("text-4xl font-bold", plan.highlight ? "text-white" : "text-foreground")} style={{ fontFamily: "var(--font-heading)" }}>${plan.price}</span>
                    <span className={cn("text-sm", plan.highlight ? "text-indigo-200" : "text-muted-foreground")}>/month</span>
                  </>
                ) : (
                  <span className={cn("text-3xl font-bold", plan.highlight ? "text-white" : "text-foreground")} style={{ fontFamily: "var(--font-heading)" }}>Custom</span>
                )}
              </div>
              <p className={cn("text-sm mb-6 leading-relaxed", plan.highlight ? "text-indigo-200" : "text-muted-foreground")}>{plan.desc}</p>
              <button className={cn(
                "w-full py-2.5 rounded-xl text-sm font-semibold transition-all mb-6",
                plan.highlight
                  ? "bg-white text-primary hover:bg-indigo-50"
                  : "bg-primary text-white hover:bg-indigo-900"
              )}>
                {plan.cta}
              </button>
              <ul className="space-y-2.5">
                {plan.features.map(f => (
                  <li key={f} className="flex items-center gap-2.5 text-sm">
                    <Check className={cn("w-4 h-4 flex-shrink-0", plan.highlight ? "text-indigo-300" : "text-emerald-500")} />
                    <span className={plan.highlight ? "text-indigo-100" : "text-foreground"}>{f}</span>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

function TestimonialsSection() {
  return (
    <section className="py-24 bg-background">
      <div className="max-w-7xl mx-auto px-6">
        <div className="text-center mb-14">
          <Badge variant="warning">Testimonials</Badge>
          <h2 className="mt-4 text-4xl font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Trusted by educators globally</h2>
        </div>
        <div className="grid md:grid-cols-3 gap-6">
          {TESTIMONIALS.map(t => (
            <Card key={t.name} className="p-6">
              <div className="flex gap-1 mb-4">
                {Array.from({ length: t.stars }).map((_, i) => <Star key={i} className="w-4 h-4 fill-amber-400 text-amber-400" />)}
              </div>
              <p className="text-sm text-foreground leading-relaxed mb-5">"{t.quote}"</p>
              <div className="flex items-center gap-3 pt-4 border-t border-border">
                <Avatar name={t.name} size="md" />
                <div>
                  <div className="text-sm font-semibold text-foreground">{t.name}</div>
                  <div className="text-xs text-muted-foreground">{t.role} · {t.org}</div>
                </div>
              </div>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
}

function LandingCTA({ onNav }: { onNav: (v: View) => void }) {
  return (
    <section className="py-24 bg-[#0A0B1E] relative overflow-hidden">
      <div className="absolute inset-0 bg-gradient-to-br from-indigo-900/40 via-transparent to-violet-900/30 pointer-events-none" />
      <div className="max-w-3xl mx-auto px-6 text-center relative z-10">
        <h2 className="text-4xl md:text-5xl font-extrabold text-white mb-4 leading-tight" style={{ fontFamily: "var(--font-heading)" }}>
          Ready to modernize your exams?
        </h2>
        <p className="text-white/55 text-lg mb-8">
          Join 400+ institutions already running faster, more transparent examinations with ExamFlow.
        </p>
        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          <Btn variant="primary" size="lg" className="!bg-indigo-600 hover:!bg-indigo-500" onClick={() => onNav("admin")}>
            Start Free Trial — No Card Needed
          </Btn>
          <Btn variant="outline" size="lg" className="!border-white/20 !text-white hover:!bg-white/[0.06]">
            <MessageSquare className="w-4 h-4" /> Talk to Sales
          </Btn>
        </div>
        <div className="mt-8 flex items-center justify-center gap-6 text-white/40 text-sm">
          <span className="flex items-center gap-1.5"><Shield className="w-3.5 h-3.5" /> SOC 2 Type II</span>
          <span className="flex items-center gap-1.5"><Lock className="w-3.5 h-3.5" /> GDPR Compliant</span>
          <span className="flex items-center gap-1.5"><Globe className="w-3.5 h-3.5" /> 40+ Countries</span>
        </div>
      </div>
    </section>
  );
}

function LandingPage({ onNav }: { onNav: (v: View) => void }) {
  return (
    <div className="overflow-y-auto h-full bg-white">
      <LandingNav onNav={onNav} />
      <HeroSection onNav={onNav} />
      <FeaturesSection />
      <HowItWorksSection />
      <PricingSection />
      <TestimonialsSection />
      <LandingCTA onNav={onNav} />
    </div>
  );
}

// ─── Admin Dashboard ──────────────────────────────────────────────────────────

const adminNav = [
  { id: "overview",   label: "Overview",    Icon: LayoutDashboard },
  { id: "exams",      label: "Exams",       Icon: FileText        },
  { id: "students",   label: "Students",    Icon: GraduationCap   },
  { id: "analytics",  label: "Analytics",   Icon: BarChart2       },
  { id: "settings",   label: "Settings",    Icon: Settings        },
];

function AdminSidebar({ active, onSelect }: { active: string; onSelect: (s: AdminSection) => void }) {
  return (
    <aside className="w-60 bg-sidebar flex flex-col h-full flex-shrink-0">
      <div className="h-16 flex items-center gap-2.5 px-5 border-b border-sidebar-border">
        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
          <Layers className="w-4 h-4 text-white" />
        </div>
        <div>
          <div className="text-white text-sm font-bold leading-none" style={{ fontFamily: "var(--font-heading)" }}>ExamFlow</div>
          <div className="text-indigo-300/60 text-[10px] mt-0.5">Westbridge Academy</div>
        </div>
      </div>
      <nav className="flex-1 py-4 px-3 space-y-0.5">
        {adminNav.map(({ id, label, Icon }) => (
          <button
            key={id}
            onClick={() => onSelect(id as AdminSection)}
            className={cn(
              "w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all text-left cursor-pointer",
              active === id
                ? "bg-sidebar-accent text-white font-medium"
                : "text-sidebar-foreground/70 hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"
            )}
          >
            <Icon className="w-4 h-4 flex-shrink-0" />
            {label}
          </button>
        ))}
      </nav>
      <div className="p-4 border-t border-sidebar-border">
        <div className="flex items-center gap-3 px-2">
          <Avatar name="Alex Johnson" size="sm" />
          <div className="flex-1 min-w-0">
            <div className="text-white text-xs font-medium truncate">Alex Johnson</div>
            <div className="text-indigo-300/60 text-[10px]">Admin</div>
          </div>
        </div>
      </div>
    </aside>
  );
}

function AdminOverview() {
  return (
    <div className="p-7 space-y-6 overflow-y-auto h-full">
      <div>
        <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Good morning, Alex 👋</h1>
        <p className="text-sm text-muted-foreground mt-0.5">Westbridge Academy — Academic Year 2025/26</p>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard label="Total Students" value="1,247" delta={{ label: "+12%", up: true }} Icon={GraduationCap} accent="indigo" />
        <StatCard label="Teachers" value="89" delta={{ label: "+3", up: true }} Icon={Users} accent="violet" />
        <StatCard label="Exams This Term" value="34" delta={{ label: "+8", up: true }} Icon={FileText} accent="sky" />
        <StatCard label="Pending Grading" value="3" delta={{ label: "−2", up: true }} Icon={Clock} accent="amber" />
      </div>

      <div className="grid lg:grid-cols-3 gap-5">
        <Card className="lg:col-span-2 p-5">
          <div className="flex items-center justify-between mb-5">
            <h3 className="font-semibold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Class Performance Trend</h3>
            <Badge variant="info">Last 6 months</Badge>
          </div>
          <ResponsiveContainer width="100%" height={200}>
            <AreaChart data={perfData} margin={{ top: 0, right: 0, bottom: 0, left: -20 }}>
              <defs>
                <linearGradient id="avgGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#3730A3" stopOpacity={0.15} />
                  <stop offset="95%" stopColor="#3730A3" stopOpacity={0} />
                </linearGradient>
                <linearGradient id="topGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#0EA5E9" stopOpacity={0.15} />
                  <stop offset="95%" stopColor="#0EA5E9" stopOpacity={0} />
                </linearGradient>
              </defs>
              <XAxis dataKey="month" tick={{ fontSize: 11, fill: "#6B7094" }} axisLine={false} tickLine={false} />
              <YAxis tick={{ fontSize: 11, fill: "#6B7094" }} axisLine={false} tickLine={false} domain={[60, 100]} />
              <Tooltip
                contentStyle={{ background: "#fff", border: "1px solid rgba(55,48,163,0.12)", borderRadius: 8, fontSize: 12 }}
                labelStyle={{ fontWeight: 600, color: "#0C0B24" }}
              />
              <Area type="monotone" dataKey="avg" name="Class Avg" stroke="#3730A3" strokeWidth={2} fill="url(#avgGrad)" />
              <Area type="monotone" dataKey="top" name="Top Scorer" stroke="#0EA5E9" strokeWidth={2} fill="url(#topGrad)" />
            </AreaChart>
          </ResponsiveContainer>
        </Card>

        <Card className="p-5">
          <h3 className="font-semibold text-foreground mb-5" style={{ fontFamily: "var(--font-heading)" }}>Grade Distribution</h3>
          <ResponsiveContainer width="100%" height={160}>
            <PieChart>
              <Pie data={pieData} cx="50%" cy="50%" innerRadius={50} outerRadius={70} paddingAngle={3} dataKey="value">
                {pieData.map((d, i) => <Cell key={i} fill={d.fill} />)}
              </Pie>
              <Tooltip contentStyle={{ background: "#fff", border: "1px solid rgba(55,48,163,0.12)", borderRadius: 8, fontSize: 12 }} />
            </PieChart>
          </ResponsiveContainer>
          <div className="space-y-1.5 mt-2">
            {pieData.map(d => (
              <div key={d.name} className="flex items-center justify-between text-xs">
                <div className="flex items-center gap-2">
                  <span className="w-2.5 h-2.5 rounded-sm" style={{ background: d.fill }} />
                  <span className="text-muted-foreground">{d.name}</span>
                </div>
                <span className="font-medium text-foreground">{d.value}%</span>
              </div>
            ))}
          </div>
        </Card>
      </div>

      <Card>
        <div className="p-5 border-b border-border flex items-center justify-between">
          <h3 className="font-semibold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Recent Exams</h3>
          <Btn variant="outline" size="xs"><Filter className="w-3.5 h-3.5" /> Filter</Btn>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-border bg-muted/40">
                <th className="text-left px-5 py-3 text-xs font-semibold text-muted-foreground">Exam</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground">Date</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground">Students</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground">Teacher</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground">Status</th>
                <th className="px-4 py-3" />
              </tr>
            </thead>
            <tbody>
              {EXAMS.map(e => (
                <tr key={e.id} className="border-b border-border last:border-0 hover:bg-muted/30 transition-colors">
                  <td className="px-5 py-3.5">
                    <div className="font-medium text-foreground">{e.name}</div>
                    <div className="text-xs text-muted-foreground">{e.subject}</div>
                  </td>
                  <td className="px-4 py-3.5 text-muted-foreground whitespace-nowrap">{e.date}</td>
                  <td className="px-4 py-3.5 font-medium text-foreground">{e.students}</td>
                  <td className="px-4 py-3.5">
                    <div className="flex items-center gap-2">
                      <Avatar name={e.teacher} size="xs" />
                      <span className="text-muted-foreground text-xs">{e.teacher}</span>
                    </div>
                  </td>
                  <td className="px-4 py-3.5"><StatusBadge status={e.status} /></td>
                  <td className="px-4 py-3.5">
                    <button className="p-1.5 hover:bg-muted rounded-lg transition-colors text-muted-foreground cursor-pointer">
                      <MoreHorizontal className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}

function AdminStudents() {
  return (
    <div className="p-7 space-y-5 overflow-y-auto h-full">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Students</h1>
          <p className="text-sm text-muted-foreground mt-0.5">1,247 enrolled students</p>
        </div>
        <div className="flex items-center gap-2">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <input placeholder="Search students…" className="pl-9 pr-4 h-9 text-sm rounded-lg border border-border bg-card text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring/30 w-56" />
          </div>
          <Btn variant="primary" size="sm"><Upload className="w-3.5 h-3.5" /> Import</Btn>
          <Btn variant="secondary" size="sm"><Plus className="w-3.5 h-3.5" /> Add Student</Btn>
        </div>
      </div>

      <Card>
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-border bg-muted/40">
                <th className="text-left px-5 py-3 text-xs font-semibold text-muted-foreground">Student</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground">ID</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground">Class</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground">GPA</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground">Exams</th>
                <th className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground">Status</th>
                <th className="px-4 py-3" />
              </tr>
            </thead>
            <tbody>
              {STUDENTS.map(s => (
                <tr key={s.id} className="border-b border-border last:border-0 hover:bg-muted/30 transition-colors group">
                  <td className="px-5 py-3.5">
                    <div className="flex items-center gap-3">
                      <Avatar name={s.name} size="sm" />
                      <span className="font-medium text-foreground">{s.name}</span>
                    </div>
                  </td>
                  <td className="px-4 py-3.5 font-mono text-xs text-muted-foreground">{s.idNo}</td>
                  <td className="px-4 py-3.5 text-muted-foreground">{s.cls}</td>
                  <td className="px-4 py-3.5">
                    <span className={cn("font-semibold", s.gpa >= 3.8 ? "text-emerald-600" : s.gpa >= 3.5 ? "text-indigo-600" : "text-amber-600")}>{s.gpa.toFixed(1)}</span>
                  </td>
                  <td className="px-4 py-3.5 text-foreground font-medium">{s.exams}</td>
                  <td className="px-4 py-3.5"><StatusBadge status="active" /></td>
                  <td className="px-4 py-3.5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button className="text-xs text-primary flex items-center gap-1 cursor-pointer">View <ChevronRight className="w-3.5 h-3.5" /></button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}

function AdminAnalytics() {
  return (
    <div className="p-7 space-y-5 overflow-y-auto h-full">
      <div>
        <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Analytics</h1>
        <p className="text-sm text-muted-foreground mt-0.5">Academic year 2025/26 performance overview</p>
      </div>
      <div className="grid lg:grid-cols-2 gap-5">
        <Card className="p-5">
          <h3 className="font-semibold text-foreground mb-5" style={{ fontFamily: "var(--font-heading)" }}>Grade Distribution by Subject</h3>
          <ResponsiveContainer width="100%" height={220}>
            <BarChart data={gradeData} margin={{ top: 0, right: 0, bottom: 0, left: -20 }}>
              <XAxis dataKey="grade" tick={{ fontSize: 11, fill: "#6B7094" }} axisLine={false} tickLine={false} />
              <YAxis tick={{ fontSize: 11, fill: "#6B7094" }} axisLine={false} tickLine={false} />
              <Tooltip contentStyle={{ background: "#fff", border: "1px solid rgba(55,48,163,0.12)", borderRadius: 8, fontSize: 12 }} />
              <Bar dataKey="count" name="Students" fill="#4338CA" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </Card>

        <Card className="p-5">
          <h3 className="font-semibold text-foreground mb-5" style={{ fontFamily: "var(--font-heading)" }}>Subject Strength Radar</h3>
          <ResponsiveContainer width="100%" height={220}>
            <RadarChart data={radarData} margin={{ top: 10, right: 20, bottom: 10, left: 20 }}>
              <PolarGrid stroke="rgba(55,48,163,0.1)" />
              <PolarAngleAxis dataKey="subject" tick={{ fontSize: 11, fill: "#6B7094" }} />
              <Radar name="Avg Score" dataKey="score" stroke="#4338CA" fill="#4338CA" fillOpacity={0.15} strokeWidth={2} />
              <Tooltip contentStyle={{ background: "#fff", border: "1px solid rgba(55,48,163,0.12)", borderRadius: 8, fontSize: 12 }} />
            </RadarChart>
          </ResponsiveContainer>
        </Card>

        <Card className="p-5 lg:col-span-2">
          <h3 className="font-semibold text-foreground mb-5" style={{ fontFamily: "var(--font-heading)" }}>Monthly Average Score Trend</h3>
          <ResponsiveContainer width="100%" height={180}>
            <AreaChart data={perfData} margin={{ top: 0, right: 0, bottom: 0, left: -20 }}>
              <defs>
                <linearGradient id="grad2" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#4338CA" stopOpacity={0.15} />
                  <stop offset="95%" stopColor="#4338CA" stopOpacity={0} />
                </linearGradient>
              </defs>
              <XAxis dataKey="month" tick={{ fontSize: 11, fill: "#6B7094" }} axisLine={false} tickLine={false} />
              <YAxis tick={{ fontSize: 11, fill: "#6B7094" }} axisLine={false} tickLine={false} domain={[65, 100]} />
              <Tooltip contentStyle={{ background: "#fff", border: "1px solid rgba(55,48,163,0.12)", borderRadius: 8, fontSize: 12 }} />
              <Area type="monotone" dataKey="avg" name="Class Avg" stroke="#4338CA" strokeWidth={2.5} fill="url(#grad2)" />
            </AreaChart>
          </ResponsiveContainer>
        </Card>
      </div>
    </div>
  );
}

function AdminDashboard({ onNav }: { onNav: (v: View) => void }) {
  const [section, setSection] = useState<AdminSection>("overview");
  const content = {
    overview: <AdminOverview />,
    exams: <AdminOverview />,
    students: <AdminStudents />,
    analytics: <AdminAnalytics />,
    settings: (
      <div className="p-7 flex items-center justify-center h-full">
        <div className="text-center">
          <Settings className="w-10 h-10 text-muted-foreground mx-auto mb-3 opacity-40" />
          <p className="text-muted-foreground text-sm">Settings panel</p>
        </div>
      </div>
    ),
  }[section];

  return (
    <div className="flex h-full overflow-hidden">
      <AdminSidebar active={section} onSelect={setSection} />
      <div className="flex-1 overflow-hidden flex flex-col bg-background">
        <header className="h-14 border-b border-border flex items-center justify-between px-6 bg-card flex-shrink-0">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <input placeholder="Search anything…" className="pl-9 pr-4 h-8 text-sm rounded-lg border border-border bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring/30 w-56" />
          </div>
          <div className="flex items-center gap-3">
            <Btn variant="primary" size="sm" onClick={() => onNav("landing")}><Globe className="w-3.5 h-3.5" /> View Landing</Btn>
            <button className="relative p-2 hover:bg-muted rounded-lg transition-colors cursor-pointer">
              <Bell className="w-4 h-4 text-muted-foreground" />
              <span className="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-rose-500" />
            </button>
            <Avatar name="Alex Johnson" size="sm" />
          </div>
        </header>
        <div className="flex-1 overflow-hidden">{content}</div>
      </div>
    </div>
  );
}

// ─── Teacher Workspace ────────────────────────────────────────────────────────

function TeacherWorkspace({ onNav }: { onNav: (v: View) => void }) {
  const [scores, setScores] = useState<Record<number, number>>(
    Object.fromEntries(QUESTIONS.map(q => [q.no, q.awarded]))
  );
  const [activeQ, setActiveQ] = useState(1);
  const [saved, setSaved] = useState(false);
  const total = QUESTIONS.reduce((s, q) => s + (scores[q.no] ?? 0), 0);
  const maxTotal = QUESTIONS.reduce((s, q) => s + q.max, 0);

  function handleSave() {
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  }

  return (
    <div className="flex flex-col h-full bg-background overflow-hidden">
      {/* Toolbar */}
      <header className="h-14 bg-card border-b border-border flex items-center px-5 gap-4 flex-shrink-0">
        <div className="flex items-center gap-2.5 mr-4">
          <div className="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
            <Layers className="w-3.5 h-3.5 text-white" />
          </div>
          <span className="font-bold text-sm text-foreground" style={{ fontFamily: "var(--font-heading)" }}>ExamFlow</span>
        </div>
        <div className="h-4 w-px bg-border" />
        <div>
          <div className="text-sm font-semibold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Mathematics Advanced — Correction</div>
          <div className="text-xs text-muted-foreground">Marcus Williams · STU-3192 · Aug 15, 2026</div>
        </div>
        <div className="ml-auto flex items-center gap-2">
          <div className="flex items-center gap-1.5 bg-muted rounded-lg p-1">
            {[ZoomIn, Eye, Pencil, Edit3].map((Icon, i) => (
              <button key={i} className={cn("p-1.5 rounded-md transition-colors cursor-pointer", i === 2 ? "bg-white shadow-sm text-primary" : "text-muted-foreground hover:text-foreground hover:bg-white/60")}>
                <Icon className="w-3.5 h-3.5" />
              </button>
            ))}
          </div>
          <Btn variant="outline" size="sm" onClick={() => onNav("admin")}><ChevronRight className="w-3.5 h-3.5 rotate-180" /> Dashboard</Btn>
          <Btn variant="primary" size="sm" onClick={handleSave}>
            {saved ? <><Check className="w-3.5 h-3.5" /> Saved!</> : <><Save className="w-3.5 h-3.5" /> Save</>}
          </Btn>
          <Btn variant="secondary" size="sm"><CheckCircle className="w-3.5 h-3.5 text-emerald-600" /> Submit</Btn>
        </div>
      </header>

      <div className="flex flex-1 overflow-hidden">
        {/* Answer Paper */}
        <div className="flex-1 overflow-y-auto bg-gray-100 p-6">
          <div className="max-w-2xl mx-auto">
            <div className="bg-white rounded-xl shadow-md overflow-hidden">
              <div className="bg-indigo-700 px-6 py-4 text-white">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-xs text-indigo-200 mb-0.5">Westbridge Academy · AY 2025/26</div>
                    <div className="font-bold text-lg" style={{ fontFamily: "var(--font-heading)" }}>Mathematics Advanced — Final Examination</div>
                  </div>
                  <div className="text-right">
                    <div className="text-xs text-indigo-200">Student ID</div>
                    <div className="font-mono font-bold">STU-3192</div>
                  </div>
                </div>
              </div>

              <div className="p-6 space-y-6">
                {QUESTIONS.map(q => (
                  <div
                    key={q.no}
                    onClick={() => setActiveQ(q.no)}
                    className={cn(
                      "border-2 rounded-xl p-4 cursor-pointer transition-all",
                      activeQ === q.no ? "border-indigo-400 bg-indigo-50/50" : "border-gray-200 hover:border-indigo-200"
                    )}
                  >
                    <div className="flex items-start justify-between gap-3 mb-3">
                      <div className="flex items-center gap-2.5">
                        <span className="w-7 h-7 rounded-lg bg-indigo-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
                          {q.no}
                        </span>
                        <span className="text-sm text-foreground font-medium">{q.text}</span>
                      </div>
                      <div className="flex items-center gap-1 flex-shrink-0">
                        <span className="font-bold text-indigo-700 text-sm" style={{ fontFamily: "var(--font-mono)" }}>{scores[q.no]}</span>
                        <span className="text-muted-foreground text-xs">/ {q.max}</span>
                      </div>
                    </div>
                    {/* Simulated handwritten answer lines */}
                    <div className="ml-9 space-y-1.5 mb-3">
                      {[0.85, 0.7, 0.9, 0.6].map((w, i) => (
                        <div key={i} className="h-3 rounded-sm bg-gray-200" style={{ width: `${w * 100}%` }} />
                      ))}
                    </div>
                    {activeQ === q.no && (
                      <div className="ml-9 mt-2 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-xs text-amber-800">
                        <span className="font-semibold">Teacher note:</span> {q.comment}
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>

        {/* Grading Panel */}
        <div className="w-80 bg-card border-l border-border flex flex-col flex-shrink-0 overflow-hidden">
          <div className="p-4 border-b border-border">
            <div className="flex items-center justify-between mb-3">
              <div className="text-sm font-semibold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Question Scores</div>
              <div className="text-xs text-muted-foreground font-mono">{total} / {maxTotal}</div>
            </div>
            <div className="w-full bg-muted rounded-full h-1.5">
              <div className="bg-indigo-600 h-1.5 rounded-full transition-all" style={{ width: `${(total / maxTotal) * 100}%` }} />
            </div>
          </div>

          <div className="flex-1 overflow-y-auto p-4 space-y-3">
            {QUESTIONS.map(q => (
              <div
                key={q.no}
                onClick={() => setActiveQ(q.no)}
                className={cn(
                  "rounded-xl p-3.5 border cursor-pointer transition-all",
                  activeQ === q.no ? "border-indigo-300 bg-indigo-50" : "border-border hover:border-indigo-200 hover:bg-muted/30"
                )}
              >
                <div className="flex items-center justify-between mb-2">
                  <span className="text-xs font-semibold text-foreground">Question {q.no}</span>
                  <Badge variant={scores[q.no] === q.max ? "success" : scores[q.no] >= q.max * 0.7 ? "info" : "warning"}>
                    {scores[q.no]}/{q.max}
                  </Badge>
                </div>
                <p className="text-xs text-muted-foreground mb-3 line-clamp-2">{q.text}</p>
                <div className="flex items-center gap-2">
                  <span className="text-xs text-muted-foreground">Mark:</span>
                  <div className="flex items-center gap-1">
                    <button
                      onClick={(e) => { e.stopPropagation(); setScores(p => ({ ...p, [q.no]: Math.max(0, (p[q.no] ?? 0) - 1) })); }}
                      className="w-6 h-6 rounded-md bg-muted hover:bg-border flex items-center justify-center text-foreground cursor-pointer text-sm font-bold"
                    >−</button>
                    <span className="w-8 text-center text-sm font-bold text-foreground" style={{ fontFamily: "var(--font-mono)" }}>{scores[q.no]}</span>
                    <button
                      onClick={(e) => { e.stopPropagation(); setScores(p => ({ ...p, [q.no]: Math.min(q.max, (p[q.no] ?? 0) + 1) })); }}
                      className="w-6 h-6 rounded-md bg-muted hover:bg-border flex items-center justify-center text-foreground cursor-pointer text-sm font-bold"
                    >+</button>
                  </div>
                </div>
                {activeQ === q.no && (
                  <input
                    className="mt-2 w-full text-xs bg-background border border-border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-ring/30 text-foreground"
                    defaultValue={q.comment}
                    placeholder="Add comment…"
                    onClick={e => e.stopPropagation()}
                  />
                )}
              </div>
            ))}
          </div>

          <div className="p-4 border-t border-border bg-muted/30">
            <div className="flex items-center justify-between mb-3">
              <span className="text-sm font-semibold text-foreground">Total Score</span>
              <div className="text-right">
                <span className="text-2xl font-bold text-indigo-700" style={{ fontFamily: "var(--font-heading)" }}>{total}</span>
                <span className="text-sm text-muted-foreground"> / {maxTotal}</span>
              </div>
            </div>
            <div className="text-xs text-muted-foreground flex items-center gap-1.5">
              <Clock className="w-3.5 h-3.5" />
              Audit log: Last saved 2 min ago · Dr. Chen Wei
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Student Portal ───────────────────────────────────────────────────────────

function StudentPortal({ onNav }: { onNav: (v: View) => void }) {
  const [tab, setTab] = useState<StudentTab>("dashboard");

  return (
    <div className="flex flex-col h-full bg-background overflow-hidden">
      {/* Header */}
      <header className="bg-card border-b border-border flex-shrink-0">
        <div className="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
              <Layers className="w-4 h-4 text-white" />
            </div>
            <span className="font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>ExamFlow</span>
          </div>
          <div className="flex items-center gap-4">
            <button className="relative p-2 hover:bg-muted rounded-lg cursor-pointer">
              <Bell className="w-4 h-4 text-muted-foreground" />
              <span className="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-rose-500" />
            </button>
            <div className="flex items-center gap-2.5">
              <Avatar name="Sophia Chen" size="sm" />
              <div className="hidden sm:block">
                <div className="text-sm font-semibold text-foreground">Sophia Chen</div>
                <div className="text-xs text-muted-foreground">STU-2847 · Year 12A</div>
              </div>
            </div>
          </div>
        </div>
        <div className="max-w-5xl mx-auto px-6 flex gap-1">
          {(["dashboard", "results", "appeals"] as StudentTab[]).map(t => (
            <button
              key={t}
              onClick={() => setTab(t)}
              className={cn(
                "px-4 py-3 text-sm font-medium capitalize border-b-2 transition-all cursor-pointer",
                tab === t ? "border-primary text-primary" : "border-transparent text-muted-foreground hover:text-foreground"
              )}
            >
              {t}
            </button>
          ))}
        </div>
      </header>

      <div className="flex-1 overflow-y-auto">
        <div className="max-w-5xl mx-auto px-6 py-7 space-y-6">
          {tab === "dashboard" && (
            <>
              {/* Welcome */}
              <div className="bg-gradient-to-br from-indigo-700 to-violet-700 rounded-2xl p-6 text-white relative overflow-hidden">
                <div className="absolute right-0 top-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2" />
                <div className="relative z-10">
                  <div className="text-indigo-200 text-sm mb-1">Welcome back</div>
                  <h2 className="text-2xl font-bold mb-3" style={{ fontFamily: "var(--font-heading)" }}>Sophia Chen</h2>
                  <div className="flex flex-wrap gap-4 text-sm">
                    <div><span className="text-indigo-200">GPA </span><span className="font-bold">3.9</span></div>
                    <div><span className="text-indigo-200">Exams completed </span><span className="font-bold">5</span></div>
                    <div><span className="text-indigo-200">Class rank </span><span className="font-bold">#2 / 38</span></div>
                  </div>
                </div>
              </div>

              {/* Upcoming */}
              <div>
                <h3 className="font-semibold text-foreground mb-3" style={{ fontFamily: "var(--font-heading)" }}>Upcoming Exams</h3>
                <div className="grid sm:grid-cols-2 gap-4">
                  {EXAMS.filter(e => e.status === "scheduled").slice(0, 2).map(e => (
                    <Card key={e.id} className="p-5">
                      <div className="flex items-start justify-between mb-3">
                        <div className="p-2 rounded-lg bg-indigo-50 text-indigo-600"><BookOpen className="w-4 h-4" /></div>
                        <StatusBadge status={e.status} />
                      </div>
                      <h4 className="font-semibold text-foreground mb-1" style={{ fontFamily: "var(--font-heading)" }}>{e.name}</h4>
                      <div className="flex items-center gap-1 text-xs text-muted-foreground mb-3">
                        <Calendar className="w-3.5 h-3.5" />
                        {e.date}
                      </div>
                      <div className="flex items-center gap-2 text-xs text-muted-foreground">
                        <Users className="w-3.5 h-3.5" />
                        {e.students} students
                      </div>
                    </Card>
                  ))}
                </div>
              </div>

              {/* Recent results */}
              <div>
                <h3 className="font-semibold text-foreground mb-3" style={{ fontFamily: "var(--font-heading)" }}>Recent Results</h3>
                <div className="space-y-3">
                  {RESULTS.map(r => (
                    <Card key={r.exam} className="p-4 flex items-center justify-between">
                      <div className="flex items-center gap-3">
                        <div className={cn("w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold", r.status === "published" ? "bg-indigo-100 text-indigo-700" : "bg-amber-50 text-amber-700")}>
                          {r.grade}
                        </div>
                        <div>
                          <div className="text-sm font-medium text-foreground">{r.exam}</div>
                          <div className="text-xs text-muted-foreground">{r.date}</div>
                        </div>
                      </div>
                      <div className="flex items-center gap-4">
                        <div className="text-right">
                          <div className="text-sm font-bold text-foreground" style={{ fontFamily: "var(--font-mono)" }}>{r.score}/{r.max}</div>
                          <div className="text-xs text-muted-foreground">{Math.round(r.score / r.max * 100)}%</div>
                        </div>
                        <StatusBadge status={r.status} />
                      </div>
                    </Card>
                  ))}
                </div>
              </div>
            </>
          )}

          {tab === "results" && (
            <>
              <div>
                <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>My Results</h1>
                <p className="text-sm text-muted-foreground mt-0.5">Detailed breakdown of all examination results</p>
              </div>

              <div className="grid lg:grid-cols-5 gap-5">
                <Card className="p-5 lg:col-span-3">
                  <h3 className="font-semibold text-foreground mb-4" style={{ fontFamily: "var(--font-heading)" }}>Subject Performance</h3>
                  <ResponsiveContainer width="100%" height={220}>
                    <RadarChart data={radarData} margin={{ top: 10, right: 20, bottom: 10, left: 20 }}>
                      <PolarGrid stroke="rgba(55,48,163,0.1)" />
                      <PolarAngleAxis dataKey="subject" tick={{ fontSize: 11, fill: "#6B7094" }} />
                      <Radar name="Your score" dataKey="score" stroke="#3730A3" fill="#3730A3" fillOpacity={0.2} strokeWidth={2} />
                      <Tooltip contentStyle={{ background: "#fff", border: "1px solid rgba(55,48,163,0.12)", borderRadius: 8, fontSize: 12 }} />
                    </RadarChart>
                  </ResponsiveContainer>
                </Card>

                <div className="lg:col-span-2 space-y-3">
                  {[
                    { label: "Overall Average", value: "80.7%", trend: "+4.2%", up: true },
                    { label: "Best Subject", value: "Physics (90%)", trend: "Top 5%", up: true },
                    { label: "Needs Attention", value: "Chemistry (68%)", trend: "−6 pts", up: false },
                    { label: "Class Rank", value: "#2 / 38", trend: "↑1 place", up: true },
                  ].map(s => (
                    <Card key={s.label} className="p-4">
                      <div className="text-xs text-muted-foreground mb-1">{s.label}</div>
                      <div className="flex items-center justify-between">
                        <div className="text-sm font-bold text-foreground">{s.value}</div>
                        <span className={cn("text-xs font-semibold flex items-center gap-0.5", s.up ? "text-emerald-600" : "text-amber-600")}>
                          {s.trend}
                        </span>
                      </div>
                    </Card>
                  ))}
                </div>
              </div>

              <div className="space-y-3">
                <h3 className="font-semibold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>All Exams</h3>
                {RESULTS.map(r => (
                  <Card key={r.exam} className="p-5">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-4">
                        <div className={cn("w-12 h-12 rounded-xl flex items-center justify-center text-base font-bold flex-shrink-0",
                          r.score >= 85 ? "bg-indigo-100 text-indigo-700" : r.score >= 70 ? "bg-sky-50 text-sky-700" : "bg-amber-50 text-amber-700")}>
                          {r.grade}
                        </div>
                        <div>
                          <div className="font-semibold text-foreground">{r.exam}</div>
                          <div className="text-xs text-muted-foreground mt-0.5">{r.date}</div>
                        </div>
                      </div>
                      <div className="flex items-center gap-5">
                        <div className="text-right hidden sm:block">
                          <div className="text-lg font-bold text-foreground" style={{ fontFamily: "var(--font-mono)" }}>{r.score}<span className="text-sm font-normal text-muted-foreground">/{r.max}</span></div>
                          <div className="w-24 bg-muted rounded-full h-1.5 mt-1">
                            <div className="bg-indigo-600 h-1.5 rounded-full" style={{ width: `${r.score}%` }} />
                          </div>
                        </div>
                        <StatusBadge status={r.status} />
                        {r.status === "published" && (
                          <button className="text-xs text-primary flex items-center gap-1 cursor-pointer hover:underline">
                            View Paper <ChevronRight className="w-3.5 h-3.5" />
                          </button>
                        )}
                      </div>
                    </div>
                  </Card>
                ))}
              </div>
            </>
          )}

          {tab === "appeals" && (
            <>
              <div className="flex items-center justify-between">
                <div>
                  <h1 className="text-2xl font-bold text-foreground" style={{ fontFamily: "var(--font-heading)" }}>Appeals</h1>
                  <p className="text-sm text-muted-foreground mt-0.5">Submit and track grade review requests</p>
                </div>
                <Btn variant="primary" size="sm"><Plus className="w-3.5 h-3.5" /> New Appeal</Btn>
              </div>

              {/* Info banner */}
              <div className="flex items-start gap-3 bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                <AlertTriangle className="w-4 h-4 text-indigo-600 flex-shrink-0 mt-0.5" />
                <p className="text-sm text-indigo-800">
                  Appeals must be submitted within <strong>14 days</strong> of result publication. All reviews are conducted by a second examiner and logged for transparency.
                </p>
              </div>

              <div className="space-y-3">
                {APPEALS.map(a => (
                  <Card key={a.id} className="p-5">
                    <div className="flex items-start justify-between gap-4 mb-3">
                      <div>
                        <div className="flex items-center gap-2 mb-1">
                          <span className="font-mono text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded">{a.id}</span>
                          <StatusBadge status={a.status} />
                        </div>
                        <h4 className="font-semibold text-foreground">{a.exam}</h4>
                        <p className="text-sm text-muted-foreground mt-0.5">{a.reason}</p>
                      </div>
                      <div className="text-right flex-shrink-0">
                        <div className="text-xs text-muted-foreground">Submitted</div>
                        <div className="text-sm font-medium text-foreground">{a.date}</div>
                      </div>
                    </div>
                    {/* Timeline */}
                    <div className="mt-4 pt-4 border-t border-border flex gap-3">
                      {["Submitted", "Under Review", a.status === "accepted" ? "Accepted" : "Decision Pending"].map((step, i, arr) => {
                        const done = i === 0 || (i === 1 && a.status !== "submitted") || (i === 2 && a.status === "accepted");
                        const current = (i === 1 && a.status === "under_review") || (i === 2 && a.status === "accepted");
                        return (
                          <div key={step} className="flex items-center gap-2 flex-1">
                            <div className={cn(
                              "w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0",
                              done ? "bg-emerald-100" : current ? "bg-amber-100" : "bg-muted"
                            )}>
                              {done ? <Check className="w-3 h-3 text-emerald-600" /> : <span className="w-2 h-2 rounded-full bg-muted-foreground" />}
                            </div>
                            <span className={cn("text-xs flex-1", done || current ? "text-foreground font-medium" : "text-muted-foreground")}>{step}</span>
                            {i < arr.length - 1 && <div className={cn("h-px flex-1 max-w-8", done ? "bg-emerald-200" : "bg-border")} />}
                          </div>
                        );
                      })}
                    </div>
                  </Card>
                ))}
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

// ─── View Switcher ─────────────────────────────────────────────────────────────

const VIEWS: { id: View; label: string; desc: string }[] = [
  { id: "landing",  label: "Landing Page",       desc: "Marketing website" },
  { id: "admin",    label: "Admin Dashboard",     desc: "Organization view"  },
  { id: "teacher",  label: "Teacher Workspace",   desc: "Correction tool"   },
  { id: "student",  label: "Student Portal",      desc: "Results & appeals" },
];

function ViewSwitcher({ active, onChange }: { active: View; onChange: (v: View) => void }) {
  const [open, setOpen] = useState(false);
  const current = VIEWS.find(v => v.id === active)!;

  return (
    <div className="relative z-50">
      <button
        onClick={() => setOpen(o => !o)}
        className="flex items-center gap-2 bg-[#0A0B1E]/80 backdrop-blur-sm text-white text-xs px-3.5 py-2 rounded-full border border-white/10 hover:border-white/25 transition-all cursor-pointer shadow-lg"
      >
        <span className="w-1.5 h-1.5 rounded-full bg-indigo-400" />
        <span className="font-medium hidden sm:inline">{current.label}</span>
        <span className="font-medium sm:hidden">View</span>
        <Menu className="w-3.5 h-3.5 opacity-60" />
      </button>
      {open && (
        <div className="absolute top-full mt-2 left-0 w-56 bg-[#0F102A] border border-white/10 rounded-xl shadow-2xl overflow-hidden">
          <div className="p-2">
            <div className="text-[10px] font-semibold text-white/30 uppercase px-2 mb-1.5 tracking-wider">Switch Product View</div>
            {VIEWS.map(v => (
              <button
                key={v.id}
                onClick={() => { onChange(v.id); setOpen(false); }}
                className={cn(
                  "w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-left transition-all cursor-pointer",
                  active === v.id ? "bg-indigo-600/30 text-indigo-300" : "text-white/70 hover:bg-white/5 hover:text-white"
                )}
              >
                <span className={cn("w-1.5 h-1.5 rounded-full flex-shrink-0", active === v.id ? "bg-indigo-400" : "bg-white/20")} />
                <div>
                  <div className="text-xs font-medium">{v.label}</div>
                  <div className="text-[10px] opacity-50">{v.desc}</div>
                </div>
                {active === v.id && <Check className="w-3.5 h-3.5 ml-auto" />}
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

// ─── App ──────────────────────────────────────────────────────────────────────

export default function App() {
  const [view, setView] = useState<View>("landing");

  const content = {
    landing: <LandingPage onNav={setView} />,
    admin:   <AdminDashboard onNav={setView} />,
    teacher: <TeacherWorkspace onNav={setView} />,
    student: <StudentPortal onNav={setView} />,
  }[view];

  return (
    <div className="w-full h-full relative overflow-hidden" style={{ fontFamily: "var(--font-body)" }}>
      {content}
      <div className="fixed bottom-5 left-1/2 -translate-x-1/2 z-50">
        <ViewSwitcher active={view} onChange={setView} />
      </div>
    </div>
  );
}
