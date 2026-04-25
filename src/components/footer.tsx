import { Mail } from "lucide-react";
import { Github, Linkedin, Twitter, Instagram } from "./brand-icons";
import { personal } from "@/lib/data";

export function Footer() {
  const year = new Date().getFullYear();
  const socials = [
    { href: personal.social.github, label: "GitHub", icon: Github },
    { href: personal.social.linkedin, label: "LinkedIn", icon: Linkedin },
    { href: personal.social.twitter, label: "Twitter", icon: Twitter },
    { href: personal.social.instagram, label: "Instagram", icon: Instagram },
    { href: `mailto:${personal.email}`, label: "Email", icon: Mail },
  ];
  return (
    <footer className="border-t border-border bg-background">
      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div className="flex flex-col items-center justify-between gap-6 md:flex-row">
          <div className="text-center md:text-left">
            <p className="text-sm font-semibold">{personal.fullName}</p>
            <p className="text-xs text-muted-foreground">
              {personal.role} · {personal.location}
            </p>
          </div>
          <div className="flex items-center gap-2">
            {socials.map(({ href, label, icon: Icon }) => (
              <a
                key={label}
                href={href}
                target="_blank"
                rel="noopener noreferrer"
                aria-label={label}
                className="inline-flex size-9 items-center justify-center rounded-full border border-border bg-card/60 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
              >
                <Icon className="size-4" />
              </a>
            ))}
          </div>
        </div>
        <div className="mt-8 flex flex-col items-center justify-between gap-2 border-t border-border pt-6 text-xs text-muted-foreground md:flex-row">
          <p>© {year} {personal.fullName}. All rights reserved.</p>
          <p>Built with Next.js, Tailwind CSS & Framer Motion.</p>
        </div>
      </div>
    </footer>
  );
}
