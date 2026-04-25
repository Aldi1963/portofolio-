"use client";

import { motion } from "framer-motion";
import { Coffee, Heart, MapPin, Briefcase } from "lucide-react";
import { personal } from "@/lib/data";
import { SectionHeading } from "../section-heading";

const facts = [
  { icon: MapPin, label: "Lokasi", value: personal.location },
  { icon: Briefcase, label: "Status", value: personal.available ? "Open for work" : "Sedang sibuk" },
  { icon: Coffee, label: "Hobby", value: "Ngopi & menulis" },
  { icon: Heart, label: "Fokus", value: "DX & UX" },
];

export function About() {
  return (
    <section id="about" className="relative py-24 sm:py-32">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          eyebrow="About"
          title={<>Sedikit cerita tentang <span className="gradient-text">saya</span></>}
          description="Latar belakang singkat, prinsip kerja, dan apa yang saya kejar di setiap proyek."
        />

        <div className="grid gap-10 lg:grid-cols-[1fr_1.4fr] lg:gap-16">
          <motion.div
            initial={{ opacity: 0, x: -30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, amount: 0.3 }}
            transition={{ duration: 0.5 }}
            className="space-y-4"
          >
            <div className="gradient-border aspect-[4/5] overflow-hidden rounded-2xl bg-card/60">
              <div className="flex h-full items-center justify-center bg-gradient-to-br from-primary/20 via-transparent to-accent/20">
                <span className="text-9xl font-bold gradient-text">
                  {personal.name.charAt(0)}
                </span>
              </div>
            </div>
            <div className="grid grid-cols-2 gap-3">
              {facts.map(({ icon: Icon, label, value }) => (
                <div
                  key={label}
                  className="rounded-xl border border-border bg-card/60 p-3"
                >
                  <Icon className="size-4 text-primary" />
                  <p className="mt-2 text-[10px] uppercase tracking-wider text-muted-foreground">
                    {label}
                  </p>
                  <p className="text-sm font-medium">{value}</p>
                </div>
              ))}
            </div>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, x: 30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, amount: 0.3 }}
            transition={{ duration: 0.5, delay: 0.1 }}
            className="space-y-6"
          >
            <p className="text-pretty text-lg leading-relaxed text-muted-foreground">
              {personal.bio}
            </p>
            <p className="text-pretty text-base leading-relaxed text-muted-foreground">
              {personal.longBio}
            </p>

            <div className="grid gap-4 sm:grid-cols-2">
              <div className="rounded-xl border border-border bg-card/60 p-5">
                <h3 className="text-sm font-semibold">Apa yang saya kejar</h3>
                <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                  <li className="flex gap-2"><span className="text-primary">→</span> Kode yang readable & maintainable</li>
                  <li className="flex gap-2"><span className="text-primary">→</span> UX yang halus & cepat</li>
                  <li className="flex gap-2"><span className="text-primary">→</span> Solusi yang menyelesaikan masalah nyata</li>
                </ul>
              </div>
              <div className="rounded-xl border border-border bg-card/60 p-5">
                <h3 className="text-sm font-semibold">Prinsip kerja</h3>
                <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                  <li className="flex gap-2"><span className="text-accent">✦</span> Komunikasi proaktif</li>
                  <li className="flex gap-2"><span className="text-accent">✦</span> Iterasi cepat & feedback dini</li>
                  <li className="flex gap-2"><span className="text-accent">✦</span> Belajar terus, tetap rendah hati</li>
                </ul>
              </div>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
