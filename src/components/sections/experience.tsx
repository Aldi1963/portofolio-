"use client";

import { motion } from "framer-motion";
import { Briefcase, GraduationCap, MapPin } from "lucide-react";
import { experience, education } from "@/lib/data";
import { SectionHeading } from "../section-heading";

export function Experience() {
  return (
    <section id="experience" className="relative py-24 sm:py-32">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          eyebrow="Journey"
          title={<>Pengalaman & <span className="gradient-text">pendidikan</span></>}
          description="Perjalanan karier dan latar belakang pendidikan saya."
        />

        <div className="grid gap-12 lg:grid-cols-2 lg:gap-16">
          <Timeline
            title="Experience"
            icon={Briefcase}
            items={experience.map((e) => ({
              title: e.role,
              subtitle: e.company,
              period: `${e.start} – ${e.end}`,
              location: e.location,
              current: !!e.current,
              description: e.description,
              bullets: e.highlights,
            }))}
          />
          <Timeline
            title="Education"
            icon={GraduationCap}
            items={education.map((e) => ({
              title: e.degree,
              subtitle: e.school,
              period: `${e.start} – ${e.end}`,
              location: e.location,
              current: false,
              description: e.description ?? "",
              bullets: [],
            }))}
          />
        </div>
      </div>
    </section>
  );
}

function Timeline({
  title,
  icon: Icon,
  items,
}: {
  title: string;
  icon: React.ComponentType<{ className?: string }>;
  items: Array<{
    title: string;
    subtitle: string;
    period: string;
    location: string;
    current: boolean;
    description: string;
    bullets: string[];
  }>;
}) {
  return (
    <div>
      <h3 className="mb-8 inline-flex items-center gap-2 text-lg font-semibold">
        <Icon className="size-5 text-primary" /> {title}
      </h3>
      <ol className="relative space-y-8 border-l border-border pl-6">
        {items.map((it, i) => (
          <motion.li
            key={`${it.title}-${i}`}
            initial={{ opacity: 0, x: -20 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, amount: 0.3 }}
            transition={{ duration: 0.4, delay: i * 0.05 }}
            className="relative"
          >
            <span className="absolute -left-[31px] top-1.5 flex size-4 items-center justify-center rounded-full border-2 border-background bg-primary">
              {it.current && (
                <span className="absolute size-4 animate-ping rounded-full bg-primary/60" />
              )}
            </span>
            <div className="rounded-xl border border-border bg-card/60 p-5">
              <div className="flex flex-wrap items-start justify-between gap-2">
                <div>
                  <h4 className="font-semibold">{it.title}</h4>
                  <p className="text-sm text-muted-foreground">{it.subtitle}</p>
                </div>
                <div className="text-right text-xs text-muted-foreground">
                  <p>{it.period}</p>
                  <p className="mt-0.5 inline-flex items-center gap-1">
                    <MapPin className="size-3" /> {it.location}
                  </p>
                </div>
              </div>
              {it.description && (
                <p className="mt-3 text-sm text-muted-foreground">{it.description}</p>
              )}
              {it.bullets.length > 0 && (
                <ul className="mt-3 space-y-1.5 text-sm text-muted-foreground">
                  {it.bullets.map((b) => (
                    <li key={b} className="flex gap-2">
                      <span className="text-primary">▸</span>
                      <span>{b}</span>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          </motion.li>
        ))}
      </ol>
    </div>
  );
}
