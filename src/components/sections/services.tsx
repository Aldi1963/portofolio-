"use client";

import { motion } from "framer-motion";
import {
  Code,
  Server,
  Palette,
  Zap,
  Users,
  Wrench,
  type LucideIcon,
} from "lucide-react";
import { services } from "@/lib/data";
import { SectionHeading } from "../section-heading";

const iconMap: Record<string, LucideIcon> = {
  code: Code,
  server: Server,
  palette: Palette,
  zap: Zap,
  users: Users,
  wrench: Wrench,
};

export function Services() {
  return (
    <section id="services" className="relative py-24 sm:py-32">
      <div className="absolute inset-0 -z-10 bg-grid opacity-40" />
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          eyebrow="Services"
          title={<>Yang bisa saya <span className="gradient-text">bantu</span></>}
          description="Kalau Anda butuh salah satu dari yang berikut, saya senang bantu."
        />

        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {services.map((s, i) => {
            const Icon = iconMap[s.icon] ?? Code;
            return (
              <motion.div
                key={s.title}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, amount: 0.2 }}
                transition={{ duration: 0.4, delay: i * 0.05 }}
                className="group relative overflow-hidden rounded-2xl border border-border bg-card/60 p-6 transition-all hover:-translate-y-1 hover:border-primary/40"
              >
                <div className="mb-4 inline-flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-accent text-primary-foreground">
                  <Icon className="size-5" />
                </div>
                <h3 className="text-base font-semibold">{s.title}</h3>
                <p className="mt-2 text-sm leading-relaxed text-muted-foreground">
                  {s.description}
                </p>
                <div className="absolute -right-12 -top-12 size-32 rounded-full bg-primary/0 blur-3xl transition-all group-hover:bg-primary/20" />
              </motion.div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
